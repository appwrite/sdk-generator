package docker

import (
	"archive/tar"
	"compress/gzip"
	"context"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/config"
)

// The build, hot-swap and cleanup flows that sit on top of Client.
//
// Ports the remaining halves of templates/cli/lib/emulation/docker.ts and the
// reload branch of lib/commands/run.ts.

// Emulator runs one function locally.
type Emulator struct {
	Client   *Client
	Local    *config.Local
	Function config.Function
	// Directory is the resolved function directory.
	Directory string
}

// NewEmulator resolves a function's directory and returns an emulator for it.
func NewEmulator(client *Client, local *config.Local, function config.Function) *Emulator {
	return &Emulator{
		Client:    client,
		Local:     local,
		Function:  function,
		Directory: local.ResolveResourcePath("functions", function.Path),
	}
}

// scratch is a path inside the function's .appwrite directory.
func (e *Emulator) scratch(parts ...string) string {
	return ScratchPath(e.Directory, parts...)
}

// PrepareScratch creates the .appwrite directory and the two log files the
// container bind-mounts.
//
// They must exist as FILES before the container starts: Docker creates a
// missing bind-mount source as a directory, and the runtime then fails to open
// its log with an error that points nowhere useful.
func (e *Emulator) PrepareScratch() error {
	if err := os.MkdirAll(e.scratch(), 0o755); err != nil {
		return err
	}

	for _, name := range []string{"logs.txt", "errors.txt"} {
		path := e.scratch(name)
		if _, err := os.Stat(path); err == nil {
			continue
		}
		if err := os.WriteFile(path, nil, 0o644); err != nil {
			return err
		}
	}

	return nil
}

// Cleanup stops the container and removes everything the emulation wrote.
func (e *Emulator) Cleanup(ctx context.Context) error {
	e.Client.Stop(ctx, e.Function.ID)

	if err := os.RemoveAll(e.scratch()); err != nil {
		return err
	}

	// The runtime image writes code.tar.gz into the function directory itself
	// on some paths; left behind it would be packed into the next build.
	return os.Remove(filepath.Join(e.Directory, "code.tar.gz"))
}

// Build stages the function's sources and builds them in the runtime image.
//
// Sources are copied to a staging directory rather than bind-mounting the
// function directory: the build writes code.tar.gz into /mnt/code, and mounting
// the real directory would drop that artefact into the user's source tree.
func (e *Emulator) Build(
	ctx context.Context,
	keys []string,
	variables map[string]string,
	cancelled func() bool,
) (bool, error) {
	source, err := CollectSource(e.Local, e.Function)
	if err != nil {
		return false, err
	}

	stage := e.scratch("tmp-build")
	if err := os.MkdirAll(stage, 0o755); err != nil {
		return false, err
	}

	// Removed on every exit, including the cancelled one -- a stale stage would
	// be packed into the next build.
	defer os.RemoveAll(stage)
	defer e.Client.Stop(context.WithoutCancel(ctx), e.Function.ID)

	if err := CopyInto(stage, source.Directory, source.Files); err != nil {
		return false, err
	}

	aborted, err := e.Client.Build(ctx, BuildOptions{
		ID:               e.Function.ID,
		StagePath:        stage,
		Image:            ImageName(e.Function),
		Entrypoint:       e.Function.Entrypoint,
		Commands:         e.Function.Commands,
		WorkingDirectory: e.Directory,
		VariableKeys:     keys,
		Variables:        variables,
		Cancelled:        cancelled,
	})
	if aborted || err != nil {
		return aborted, err
	}

	if err := os.MkdirAll(e.scratch(), 0o755); err != nil {
		return false, err
	}

	return false, e.Client.CopyOut(ctx,
		e.Function.ID, "/mnt/code/code.tar.gz", e.scratch("build.tar.gz"), e.Directory)
}

// Start runs the built bundle.
func (e *Emulator) Start(
	ctx context.Context,
	port int,
	keys []string,
	variables map[string]string,
) (func() error, error) {
	tool, ok := Tool(e.Function.RuntimeName())
	if !ok {
		return nil, fmt.Errorf("unknown runtime '%s'", e.Function.Runtime)
	}

	return e.Client.Start(ctx, StartOptions{
		ID:                e.Function.ID,
		Image:             ImageName(e.Function),
		Port:              port,
		FunctionDirectory: e.Directory,
		Entrypoint:        e.Function.Entrypoint,
		StartCommand:      tool.StartCommand,
		VariableKeys:      keys,
		Variables:         variables,
	})
}

// HotSwap replaces the sources inside an existing build without rebuilding.
//
// Only valid for an interpreted runtime: the bundle is unpacked, the current
// sources are copied over it, and it is repacked. A compiled runtime needs its
// build step re-run, which is why the caller checks SystemTool.Compiled first.
func (e *Emulator) HotSwap() error {
	source, err := CollectSource(e.Local, e.Function)
	if err != nil {
		return err
	}

	staging := e.scratch("hot-swap")
	if err := os.RemoveAll(staging); err != nil {
		return err
	}
	if err := os.MkdirAll(staging, 0o755); err != nil {
		return err
	}

	bundle := e.scratch("build.tar.gz")
	if err := extractTarGz(bundle, staging); err != nil {
		return err
	}

	if err := CopyInto(staging, source.Directory, source.Files); err != nil {
		return err
	}

	return createTarGz(bundle, staging)
}

// extractTarGz unpacks an archive into a directory.
func extractTarGz(archive, destination string) error {
	file, err := os.Open(archive)
	if err != nil {
		return err
	}
	defer file.Close()

	decompressed, err := gzip.NewReader(file)
	if err != nil {
		return err
	}
	defer decompressed.Close()

	reader := tar.NewReader(decompressed)
	for {
		header, err := reader.Next()
		if err == io.EOF {
			return nil
		}
		if err != nil {
			return err
		}

		target, err := safeJoin(destination, header.Name)
		if err != nil {
			return err
		}

		switch header.Typeflag {
		case tar.TypeDir:
			if err := os.MkdirAll(target, 0o755); err != nil {
				return err
			}
		case tar.TypeReg:
			if err := os.MkdirAll(filepath.Dir(target), 0o755); err != nil {
				return err
			}
			if err := writeEntry(target, reader, os.FileMode(header.Mode).Perm()); err != nil {
				return err
			}
		}
		// Other entry types -- symlinks, devices, hard links -- are skipped.
		// The bundle is repacked immediately afterwards, so dropping them
		// changes what the container sees; but honouring a symlink from an
		// archive is how a path traversal gets in, and the build output the
		// runtimes produce contains none.
	}
}

// safeJoin resolves an archive entry inside the destination, refusing to escape.
//
// An archive entry named `../../.ssh/authorized_keys` would otherwise be
// written outside the staging directory. The bundle comes from the user's own
// build, but it is still untrusted input to this process.
func safeJoin(destination, name string) (string, error) {
	target := filepath.Join(destination, filepath.FromSlash(name))

	relative, err := filepath.Rel(destination, target)
	if err != nil {
		return "", err
	}
	if relative == ".." || strings.HasPrefix(relative, ".."+string(filepath.Separator)) {
		return "", fmt.Errorf("archive entry %q escapes the extraction directory", name)
	}

	return target, nil
}

func writeEntry(target string, reader io.Reader, mode os.FileMode) error {
	if mode == 0 {
		mode = 0o644
	}

	file, err := os.OpenFile(target, os.O_CREATE|os.O_TRUNC|os.O_WRONLY, mode)
	if err != nil {
		return err
	}
	defer file.Close()

	_, err = io.Copy(file, reader)

	return err
}

// createTarGz packs a directory into a gzipped archive.
//
// Written to a temporary file and renamed, so an interrupted repack cannot
// leave a truncated bundle where the container expects a valid one.
func createTarGz(archive, directory string) error {
	temporary, err := os.CreateTemp(filepath.Dir(archive), ".build-*.tar.gz")
	if err != nil {
		return err
	}
	temporaryPath := temporary.Name()
	defer os.Remove(temporaryPath)

	compressed := gzip.NewWriter(temporary)
	writer := tar.NewWriter(compressed)

	err = filepath.WalkDir(directory, func(path string, entry os.DirEntry, err error) error {
		if err != nil {
			return err
		}

		relative, err := filepath.Rel(directory, path)
		if err != nil {
			return err
		}
		if relative == "." {
			return nil
		}

		info, err := entry.Info()
		if err != nil {
			return err
		}

		header, err := tar.FileInfoHeader(info, "")
		if err != nil {
			return err
		}
		header.Name = "./" + filepath.ToSlash(relative)
		if entry.IsDir() {
			header.Name += "/"
		}

		if err := writer.WriteHeader(header); err != nil {
			return err
		}
		if entry.IsDir() || !info.Mode().IsRegular() {
			return nil
		}

		file, err := os.Open(path)
		if err != nil {
			return err
		}
		defer file.Close()

		_, err = io.Copy(writer, file)

		return err
	})
	if err != nil {
		writer.Close()
		compressed.Close()
		temporary.Close()

		return err
	}

	if err := writer.Close(); err != nil {
		return err
	}
	if err := compressed.Close(); err != nil {
		return err
	}
	if err := temporary.Close(); err != nil {
		return err
	}

	return os.Rename(temporaryPath, archive)
}
