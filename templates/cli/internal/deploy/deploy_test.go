package deploy

import (
	"archive/tar"
	"compress/gzip"
	"io"
	"os"
	"path/filepath"
	"testing"
)

// The packaging path decides what reaches a user's project. A rule that
// over-matches drops a source file and the build fails; one that fails to match
// ships a secret. These pin both directions.

func write(t *testing.T, directory, name, contents string) {
	t.Helper()

	path := filepath.Join(directory, filepath.FromSlash(name))
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte(contents), 0o644); err != nil {
		t.Fatal(err)
	}
}

func TestPackageDirectoryExcludesIgnoredFiles(t *testing.T) {
	directory := t.TempDir()

	write(t, directory, "index.js", "export default () => {};")
	write(t, directory, "src/helper.js", "//")
	write(t, directory, ".env", "SECRET=1")
	write(t, directory, "node_modules/left-pad/index.js", "//")
	write(t, directory, ".git/config", "[core]")
	write(t, directory, ".gitignore", "node_modules\n.env\n")
	write(t, directory, "src/.gitignore", "generated.js\n")
	write(t, directory, "src/generated.js", "//")
	write(t, directory, "build/output.js", "//")

	packaged, err := PackageDirectory(directory, []string{"build"}, "", nil)
	if err != nil {
		t.Fatal(err)
	}
	defer packaged.Remove()

	// The .gitignore files are packed themselves: nothing excludes them, and
	// the deployed build reads them the same way the packaging did.
	expected := []string{".gitignore", "index.js", "src/.gitignore", "src/helper.js"}
	if len(packaged.Files) != len(expected) {
		t.Fatalf("packed %v, want %v", packaged.Files, expected)
	}
	for index, name := range expected {
		if packaged.Files[index] != name {
			t.Fatalf("packed %v, want %v", packaged.Files, expected)
		}
	}
}

// The resource's own ignore rules ADD to .gitignore rather than replacing it.
// The emulation path in internal/docker replaces; the two differ on purpose.
func TestPackageDirectoryCombinesExtraRulesWithGitignore(t *testing.T) {
	directory := t.TempDir()

	write(t, directory, "index.js", "//")
	write(t, directory, "notes.md", "#")
	write(t, directory, "coverage/report.html", "<html>")
	write(t, directory, ".gitignore", "coverage\n")

	packaged, err := PackageDirectory(directory, []string{"notes.md"}, "", nil)
	if err != nil {
		t.Fatal(err)
	}
	defer packaged.Remove()

	expected := []string{".gitignore", "index.js"}
	if len(packaged.Files) != len(expected) ||
		packaged.Files[0] != expected[0] || packaged.Files[1] != expected[1] {
		t.Fatalf("packed %v, want %v", packaged.Files, expected)
	}
}

// A nested .gitignore can re-include what the root excluded, and only within
// its own subtree.
func TestPackageDirectoryNestedNegation(t *testing.T) {
	directory := t.TempDir()

	write(t, directory, "keep/notes.log", "keep me")
	write(t, directory, "drop/notes.log", "drop me")
	write(t, directory, ".gitignore", "*.log\n")
	write(t, directory, "keep/.gitignore", "!notes.log\n")

	packaged, err := PackageDirectory(directory, nil, "", nil)
	if err != nil {
		t.Fatal(err)
	}
	defer packaged.Remove()

	expected := []string{".gitignore", "keep/.gitignore", "keep/notes.log"}
	if len(packaged.Files) != len(expected) {
		t.Fatalf("packed %v, want %v", packaged.Files, expected)
	}
	for index, name := range expected {
		if packaged.Files[index] != name {
			t.Fatalf("packed %v, want %v", packaged.Files, expected)
		}
	}
}

func TestPackageDirectoryRefusesAnEmptyResult(t *testing.T) {
	directory := t.TempDir()

	write(t, directory, "index.js", "//")
	write(t, directory, ".gitignore", "*\n")

	if _, err := PackageDirectory(directory, nil, "", nil); err == nil {
		t.Fatal("packaged a directory with no deployable files")
	}
}

func TestPackageDirectoryRoundTrips(t *testing.T) {
	directory := t.TempDir()

	contents := map[string]string{
		"index.js":         "export default () => {};",
		"src/helper.js":    "export const help = 1;",
		"assets/logo.svg":  "<svg/>",
		"deep/a/b/file.md": "# heading",
	}
	for name, body := range contents {
		write(t, directory, name, body)
	}

	packaged, err := PackageDirectory(directory, nil, "", nil)
	if err != nil {
		t.Fatal(err)
	}
	defer packaged.Remove()

	unpacked := readArchive(t, packaged.Path)

	if len(unpacked) != len(contents) {
		t.Fatalf("unpacked %d entries, want %d", len(unpacked), len(contents))
	}
	for name, body := range contents {
		if unpacked[name] != body {
			t.Fatalf("entry %s is %q, want %q", name, unpacked[name], body)
		}
	}

	info, err := os.Stat(packaged.Path)
	if err != nil {
		t.Fatal(err)
	}
	if info.Size() != packaged.Size {
		t.Fatalf("recorded size %d, archive is %d", packaged.Size, info.Size())
	}
}

// A symlink pointing outside the boundary is left out rather than followed, and
// the user is told which ones.
func TestPackageDirectorySkipsSymlinksOutsideTheBoundary(t *testing.T) {
	root := t.TempDir()
	outside := t.TempDir()

	directory := filepath.Join(root, "function")
	write(t, directory, "index.js", "//")
	write(t, outside, "secret.txt", "token")

	link := filepath.Join(directory, "secret.txt")
	if err := os.Symlink(filepath.Join(outside, "secret.txt"), link); err != nil {
		t.Skipf("symlinks unavailable: %v", err)
	}

	var warnings []string
	packaged, err := PackageDirectory(directory, nil, root, func(message string) {
		warnings = append(warnings, message)
	})
	if err != nil {
		t.Fatal(err)
	}
	defer packaged.Remove()

	if len(packaged.Files) != 1 || packaged.Files[0] != "index.js" {
		t.Fatalf("packed %v, want [index.js]", packaged.Files)
	}
	if len(warnings) != 1 {
		t.Fatalf("warnings %v, want one", warnings)
	}
}

func TestPlanChunks(t *testing.T) {
	cases := []struct {
		name   string
		size   int64
		chunks []Chunk
	}{
		{
			name:   "empty",
			size:   0,
			chunks: []Chunk{{Start: 0, End: 0}},
		},
		{
			name:   "under one chunk",
			size:   1024,
			chunks: []Chunk{{Start: 0, End: 1024}},
		},
		{
			// Exactly the chunk size is ONE request with no content-range. The
			// SDK's boundary is `<=`, and treating it as two would send a
			// second, empty chunk.
			name:   "exactly one chunk",
			size:   ChunkSize,
			chunks: []Chunk{{Start: 0, End: ChunkSize}},
		},
		{
			name: "one byte over",
			size: ChunkSize + 1,
			chunks: []Chunk{
				{Start: 0, End: ChunkSize},
				{Start: ChunkSize, End: ChunkSize + 1},
			},
		},
		{
			name: "two whole chunks",
			size: 2 * ChunkSize,
			chunks: []Chunk{
				{Start: 0, End: ChunkSize},
				{Start: ChunkSize, End: 2 * ChunkSize},
			},
		},
		{
			name: "remainder last",
			size: 2*ChunkSize + 17,
			chunks: []Chunk{
				{Start: 0, End: ChunkSize},
				{Start: ChunkSize, End: 2 * ChunkSize},
				{Start: 2 * ChunkSize, End: 2*ChunkSize + 17},
			},
		},
	}

	for _, testCase := range cases {
		t.Run(testCase.name, func(t *testing.T) {
			chunks := PlanChunks(testCase.size)

			if len(chunks) != len(testCase.chunks) {
				t.Fatalf("planned %v, want %v", chunks, testCase.chunks)
			}
			for index, chunk := range chunks {
				if chunk != testCase.chunks[index] {
					t.Fatalf("planned %v, want %v", chunks, testCase.chunks)
				}
			}

			var total int64
			for _, chunk := range chunks {
				total += chunk.Length()
			}
			if total != testCase.size {
				t.Fatalf("chunks cover %d bytes, want %d", total, testCase.size)
			}
		})
	}
}

// The header is inclusive on both ends, so the last byte is End-1. An
// off-by-one is accepted by the API and produces a corrupt archive at build
// time, which is the worst possible place to find it.
func TestChunkRange(t *testing.T) {
	size := int64(2*ChunkSize + 17)
	chunks := PlanChunks(size)

	expected := []string{
		"bytes 0-5242879/10485777",
		"bytes 5242880-10485759/10485777",
		"bytes 10485760-10485776/10485777",
	}

	for index, chunk := range chunks {
		if chunk.Range(size) != expected[index] {
			t.Fatalf("chunk %d range is %q, want %q",
				index, chunk.Range(size), expected[index])
		}
	}
}

// readArchive returns the archive's entries keyed by name.
func readArchive(t *testing.T, path string) map[string]string {
	t.Helper()

	file, err := os.Open(path)
	if err != nil {
		t.Fatal(err)
	}
	defer file.Close()

	decompressed, err := gzip.NewReader(file)
	if err != nil {
		t.Fatal(err)
	}
	defer decompressed.Close()

	entries := map[string]string{}
	reader := tar.NewReader(decompressed)
	for {
		header, err := reader.Next()
		if err == io.EOF {
			return entries
		}
		if err != nil {
			t.Fatal(err)
		}

		body, err := io.ReadAll(reader)
		if err != nil {
			t.Fatal(err)
		}
		entries[header.Name] = string(body)
	}
}
