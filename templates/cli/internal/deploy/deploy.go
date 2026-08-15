package deploy

import (
	"fmt"
	"io"
	"os"
	"path"
	"path/filepath"
	"strings"
	"sync"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/archive"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/ignore"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"golang.org/x/sync/errgroup"
)

// Implements deployment source packaging and upload. Downloading deployment
// source belongs to pull and lives in internal/cmd/pullfunction.go.

const (
	// ChunkSize is Client.CHUNK_SIZE in the console SDK the TypeScript uploads
	// through. An archive at or below it is sent as one request with no
	// content-range, and the API answers without minting an upload id.
	ChunkSize = 5 * 1024 * 1024

	// UploadConcurrency is the console SDK's CONCURRENCY: the first chunk
	// establishes the upload id, then the rest go up eight at a time.
	UploadConcurrency = 8

	// ArchiveName is the filename the archive is uploaded under, as
	// packageDirectory names its temporary file.
	ArchiveName = "code.tar.gz"

	// ArchiveContentType is the type packageDirectory tags the File with.
	ArchiveContentType = "application/gzip"

	// pollInterval is POLL_DEBOUNCE * 1.5 (deployment.ts:775). The resource
	// commands poll on their own, faster, schedule; this one is only used when
	// a caller asks pushDeployment itself to wait.
	pollInterval = 3 * time.Second
)

// Deployment statuses the poll loop treats as terminal.
const (
	StatusReady  = "ready"
	StatusFailed = "failed"
)

// Archive is a packaged deployment on disk: a path, not bytes. Reading a 369 MB
// tree into memory measured 421 MB RSS against 102 MB flat for a streamed
// upload, so the archive is never held in memory -- not here, and not in Upload.
type Archive struct {
	// Path is the archive on disk.
	Path string
	// Size is its length in bytes, which is what the chunk plan is built from.
	Size int64
	// Files are the packed paths, relative and slash-separated.
	Files []string

	directory string
}

// Remove deletes the archive and the directory holding it.
func (a *Archive) Remove() error {
	if a == nil || a.directory == "" {
		return nil
	}

	return os.RemoveAll(a.directory)
}

// PackageDirectory packs a resource directory into a gzipped archive.
//
// extraIgnoreRules is the resource's own `ignore` config, which adds to
// .gitignore rather than replacing it. (The emulation path in internal/docker
// does replace -- genuinely different, do not unify them.) projectRoot bounds
// how far a symlink may be followed.
func PackageDirectory(
	directory string,
	extraIgnoreRules []string,
	projectRoot string,
	warn func(message string),
) (*Archive, error) {
	files, skippedSymlinks, err := listDeployableFiles(directory, extraIgnoreRules, projectRoot)
	if err != nil {
		return nil, err
	}

	if len(skippedSymlinks) > 0 && warn != nil {
		warn(fmt.Sprintf(
			"Skipped %d symlink(s) pointing outside the project and left them out "+
				"of the deployment: %s",
			len(skippedSymlinks), strings.Join(skippedSymlinks, ", ")))
	}

	if len(files) == 0 {
		return nil, fmt.Errorf(
			"no deployable files found at path: %s. Check your .gitignore and ignore rules",
			directory)
	}

	// A unique directory per call, so concurrent function and site pushes can
	// never write to the same archive.
	temporary, err := os.MkdirTemp("", "appwrite-deploy-")
	if err != nil {
		return nil, err
	}

	packaged := &Archive{
		Path:      filepath.Join(temporary, ArchiveName),
		Files:     files,
		directory: temporary,
	}

	if err := archive.CreateTarGzFiles(packaged.Path, directory, files); err != nil {
		_ = packaged.Remove()

		return nil, err
	}

	info, err := os.Stat(packaged.Path)
	if err != nil {
		_ = packaged.Remove()

		return nil, err
	}
	packaged.Size = info.Size()

	return packaged, nil
}

// matcher is one ignore file and the directory its patterns are relative to.
type matcher struct {
	// baseDirectory is relative to the packaged root, and empty for the root
	// itself.
	baseDirectory string
	rules         *ignore.Matcher
}

// listDeployableFiles walks a directory and applies its ignore rules.
//
// A .gitignore in a
// SUBDIRECTORY applies to that subtree only, which is why the matchers are a
// stack rather than one merged rule set.
func listDeployableFiles(
	directory string,
	extraIgnoreRules []string,
	projectRoot string,
) (files []string, skippedSymlinks []string, err error) {
	boundary, err := resolveSymlinkBoundary(directory, projectRoot)
	if err != nil {
		return nil, nil, err
	}

	// `.git` and the resource's own rules, then the root .gitignore as a
	// separate matcher -- both are active, so a config with an `ignore` field
	// keeps honouring .gitignore.
	matchers := []matcher{
		{
			baseDirectory: "",
			rules:         ignore.New().Add(".git").AddAll(extraIgnoreRules),
		},
	}
	if rooted, ok := loadMatcher(directory, ""); ok {
		matchers = append(matchers, rooted)
	}

	walker := &walker{
		directory: directory,
		boundary:  boundary,
		ancestors: map[string]bool{},
	}
	if err := walker.walk("", matchers); err != nil {
		return nil, nil, err
	}

	return walker.files, walker.skippedSymlinks, nil
}

// loadMatcher reads the .gitignore in one directory, if there is one.
func loadMatcher(directory, baseDirectory string) (matcher, bool) {
	contents, err := os.ReadFile(
		filepath.Join(directory, filepath.FromSlash(baseDirectory), ".gitignore"))
	if err != nil {
		return matcher{}, false
	}

	return matcher{
		baseDirectory: baseDirectory,
		rules:         ignore.New().Add(string(contents)),
	}, true
}

// walker carries the state one recursive walk accumulates.
type walker struct {
	directory string
	boundary  string
	// ancestors holds the resolved directories on the current branch, so a
	// symlink cycle terminates while a shared directory can still appear under
	// two different paths.
	ancestors       map[string]bool
	files           []string
	skippedSymlinks []string
}

func (w *walker) walk(relativeDirectory string, inherited []matcher) error {
	absolute := filepath.Join(w.directory, filepath.FromSlash(relativeDirectory))

	resolved, err := filepath.EvalSymlinks(absolute)
	if err != nil {
		return err
	}
	if w.ancestors[resolved] {
		return nil
	}
	w.ancestors[resolved] = true
	defer delete(w.ancestors, resolved)

	active := inherited
	if relativeDirectory != "" {
		if local, ok := loadMatcher(w.directory, relativeDirectory); ok {
			// Appended to a copy: a sibling directory must not inherit this
			// one's .gitignore.
			active = append(append([]matcher{}, inherited...), local)
		}
	}

	// os.ReadDir sorts by name where readdirSync does not, so the CLI builds can
	// pack the same files in a different order. Archive ORDER is not part of
	// the contract -- the build unpacks the whole thing -- and a deterministic
	// order makes a packaging test worth writing.
	entries, err := os.ReadDir(absolute)
	if err != nil {
		return err
	}

	for _, entry := range entries {
		relativePath := path.Join(relativeDirectory, entry.Name())
		absolutePath := filepath.Join(w.directory, filepath.FromSlash(relativePath))

		// Stat, not the DirEntry's own type: a symlink is judged by what it
		// points at, and one pointing nowhere is skipped rather than fatal.
		info, err := os.Stat(absolutePath)
		if err != nil {
			continue
		}

		if isIgnored(relativePath, active, info.IsDir()) {
			continue
		}

		target, err := filepath.EvalSymlinks(absolutePath)
		if err != nil {
			continue
		}
		if !isPathInside(w.boundary, target) {
			w.skippedSymlinks = append(w.skippedSymlinks, relativePath)

			continue
		}

		switch {
		case info.IsDir():
			if err := w.walk(relativePath, active); err != nil {
				return err
			}
		case info.Mode().IsRegular():
			w.files = append(w.files, relativePath)
		}
	}

	return nil
}

// isIgnored asks every matcher whose subtree contains the path.
//
// Last opinion wins, and "no opinion" is not an opinion: a nested .gitignore
// that says nothing about a path leaves the root's verdict standing, while one
// that negates it overrules the root.
func isIgnored(relativePath string, matchers []matcher, isDirectory bool) bool {
	ignored := false

	for _, entry := range matchers {
		if entry.baseDirectory != "" &&
			relativePath != entry.baseDirectory &&
			!strings.HasPrefix(relativePath, entry.baseDirectory+"/") {
			continue
		}

		local := relativePath
		if entry.baseDirectory != "" {
			local = strings.TrimPrefix(
				strings.TrimPrefix(relativePath, entry.baseDirectory), "/")
		}
		if isDirectory {
			local += "/"
		}

		result := entry.rules.Test(local)
		switch {
		case result.Ignored:
			ignored = true
		case result.Unignored:
			ignored = false
		}
	}

	return ignored
}

// resolveSymlinkBoundary bounds how far a symlink may be followed.
//
// Sharing `functions/common`
// between two functions stays possible; reaching `~/.ssh` does not.
func resolveSymlinkBoundary(directory, projectRoot string) (string, error) {
	resolved, err := filepath.EvalSymlinks(directory)
	if err != nil {
		return "", err
	}
	if projectRoot == "" {
		return resolved, nil
	}

	root, err := filepath.EvalSymlinks(projectRoot)
	if err != nil {
		return resolved, nil
	}
	if isPathInside(root, resolved) {
		return root, nil
	}

	return resolved, nil
}

// isPathInside reports whether child is parent or sits under it.
func isPathInside(parent, child string) bool {
	relative, err := filepath.Rel(parent, child)
	if err != nil {
		return false
	}

	return relative == "." ||
		(!strings.HasPrefix(relative, "..") && !filepath.IsAbs(relative))
}

// Chunk is one byte range of an archive. End is exclusive.
type Chunk struct {
	Start int64
	End   int64
}

// Length is the chunk's size in bytes.
func (c Chunk) Length() int64 { return c.End - c.Start }

// Range is the content-range header value for a chunk of a total-byte upload.
//
// Inclusive on both ends, so the last byte is End-1 -- an off-by-one here is
// accepted by the API and produces a corrupt archive at build time.
func (c Chunk) Range(total int64) string {
	return fmt.Sprintf("bytes %d-%d/%d", c.Start, c.End-1, total)
}

// PlanChunks splits an upload into chunks.
//
// A size at or below ChunkSize is one chunk, which the caller sends without a
// content-range at all. Above it, every chunk is exactly ChunkSize except the
// last, which is the remainder.
func PlanChunks(size int64) []Chunk {
	if size <= ChunkSize {
		return []Chunk{
			{Start: 0, End: size},
		}
	}

	chunks := make([]Chunk, 0, (size+ChunkSize-1)/ChunkSize)
	for start := int64(0); start < size; start += ChunkSize {
		end := start + ChunkSize
		if end > size {
			end = size
		}
		chunks = append(chunks, Chunk{Start: start, End: end})
	}

	return chunks
}

// Upload sends an archive to path as a multipart form.
//
// Streamed, never buffered: every chunk is an io.SectionReader over the open
// file, so peak memory is the HTTP write buffer rather than the archive. One
// request under the chunk size, otherwise a first chunk that mints the upload id
// followed by the rest, eight at a time.
func Upload(
	api *client.Client,
	path string,
	fields []client.FormField,
	fileField string,
	packaged *Archive,
) (*jsonx.Object, error) {
	file, err := os.Open(packaged.Path)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	chunks := PlanChunks(packaged.Size)

	part := func(chunk Chunk, uploadID, contentRange string) client.UploadPart {
		return client.UploadPart{
			Path:          path,
			Fields:        fields,
			FileField:     fileField,
			FileName:      ArchiveName,
			ContentType:   ArchiveContentType,
			Content:       io.NewSectionReader(file, chunk.Start, chunk.Length()),
			ContentLength: chunk.Length(),
			Range:         contentRange,
			UploadID:      uploadID,
		}
	}

	first := jsonx.NewObject()
	if len(chunks) == 1 {
		// No content-range: an upload that fits in one request is a plain
		// multipart POST, and sending a range would make the API treat it as
		// the first chunk of a larger upload that never arrives.
		if err := api.Upload(part(chunks[0], "", ""), first); err != nil {
			return nil, err
		}

		return first, nil
	}

	if err := api.Upload(part(chunks[0], "", chunks[0].Range(packaged.Size)), first); err != nil {
		return nil, err
	}

	uploadID := first.GetString("$id")

	var (
		mutex sync.Mutex
		last  = first
		final *jsonx.Object
	)

	group := errgroup.Group{}
	group.SetLimit(UploadConcurrency)

	for _, chunk := range chunks[1:] {
		group.Go(func() error {
			response := jsonx.NewObject()
			err := api.Upload(
				part(chunk, uploadID, chunk.Range(packaged.Size)), response)
			if err != nil {
				return fmt.Errorf("upload chunk at offset %d: %w", chunk.Start, err)
			}

			mutex.Lock()
			defer mutex.Unlock()

			last = response
			if isUploadComplete(response, int64(len(chunks))) {
				final = response
			}

			return nil
		})
	}

	if err := group.Wait(); err != nil {
		return nil, err
	}

	// The completing chunk carries the finished resource; with eight in flight
	// it is not necessarily the one that returned last.
	if final != nil {
		return final, nil
	}

	return last, nil
}

// isUploadComplete reports whether a chunk response says the upload finished.
func isUploadComplete(response *jsonx.Object, planned int64) bool {
	if _, ok := response.Get("chunksUploaded"); !ok {
		return false
	}

	// chunksTotal is the API's own count and wins over the plan, which is what
	// lets a resumed upload finish on the chunk the server considers last.
	total := planned
	if _, ok := response.Get("chunksTotal"); ok {
		total = response.GetInt64("chunksTotal")
	}

	return response.GetInt64("chunksUploaded") >= total
}

// Options describes one deployment push.
type Options struct {
	// ResourcePath is the directory to package.
	ResourcePath string
	// ExtraIgnoreRules are the resource's own ignore patterns.
	ExtraIgnoreRules []string
	// ProjectRoot bounds symlink following; empty for a path outside a project.
	ProjectRoot string
	// CreateDeployment uploads the archive and returns the new deployment.
	CreateDeployment func(packaged *Archive) (*jsonx.Object, error)
	// GetDeployment re-reads a deployment while polling.
	GetDeployment func(deploymentID string) (*jsonx.Object, error)
	// PollForStatus waits for the deployment to build. The resource commands
	// leave this false and run their own loop, which also reports progress.
	PollForStatus bool
	// OnStatusUpdate is called with each status seen while polling.
	OnStatusUpdate func(status string)
	// Warn reports a non-fatal packaging problem.
	Warn func(message string)
}

// Result is what a push produced.
type Result struct {
	Deployment  *jsonx.Object
	WasPolled   bool
	FinalStatus string
}

// Push packages a directory, creates the deployment and optionally waits.
//
// The archive is removed whatever
// happens: it lives in a temporary directory, and a failed push that leaves one
// behind costs the size of the deployment on every retry.
func Push(options Options) (Result, error) {
	packaged, err := PackageDirectory(
		options.ResourcePath, options.ExtraIgnoreRules, options.ProjectRoot, options.Warn)
	if err != nil {
		return Result{}, err
	}
	defer packaged.Remove()

	deployment, err := options.CreateDeployment(packaged)
	if err != nil {
		return Result{}, err
	}

	if !options.PollForStatus || options.GetDeployment == nil {
		return Result{Deployment: deployment}, nil
	}

	deploymentID := deployment.GetString("$id")
	for {
		deployment, err = options.GetDeployment(deploymentID)
		if err != nil {
			return Result{}, err
		}

		status := deployment.GetString("status")
		if options.OnStatusUpdate != nil {
			options.OnStatusUpdate(status)
		}

		if status == StatusReady || status == StatusFailed {
			return Result{
				Deployment:  deployment,
				WasPolled:   true,
				FinalStatus: status,
			}, nil
		}

		time.Sleep(pollInterval)
	}
}
