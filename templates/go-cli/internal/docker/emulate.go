package docker

import (
	"context"
	"fmt"
	"os"
	"path/filepath"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/archive"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
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
	if err := archive.ExtractTarGz(bundle, staging); err != nil {
		return err
	}

	if err := CopyInto(staging, source.Directory, source.Files); err != nil {
		return err
	}

	return archive.CreateTarGz(bundle, staging)
}
