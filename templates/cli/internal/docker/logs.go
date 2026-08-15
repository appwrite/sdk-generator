package docker

import (
	"bytes"
	"context"
	"io"
	"os"
	"path/filepath"
	"time"
)

// runtimeLogPollInterval bounds how long a function log waits before it is
// shown. Polling keeps this portable and avoids holding the files open while
// cleanup removes .appwrite on Windows.
const runtimeLogPollInterval = 50 * time.Millisecond

// followedLogFile remembers how much of one runtime log has been emitted.
type followedLogFile struct {
	path    string
	info    os.FileInfo
	offset  int64
	pending []byte
}

// FollowRuntimeLogs emits complete lines appended to the runtime's stdout and
// stderr files until the context is cancelled.
//
// Open Runtimes writes context.log() and context.error() to bind-mounted files,
// not the container's stdout or stderr, so docker logs cannot expose them. The
// files are checked in stdout/stderr order to keep output deterministic when
// both change between polls.
func FollowRuntimeLogs(ctx context.Context, functionDirectory string, emit func(string)) {
	files := []*followedLogFile{
		{path: filepath.Join(functionDirectory, AppwriteDirectory, "logs.txt")},
		{path: filepath.Join(functionDirectory, AppwriteDirectory, "errors.txt")},
	}

	read := func() {
		for _, file := range files {
			file.read(emit)
		}
	}

	// Catch output written between the container starting and this goroutine
	// being scheduled.
	read()

	ticker := time.NewTicker(runtimeLogPollInterval)
	defer ticker.Stop()

	for {
		select {
		case <-ctx.Done():
			return
		case <-ticker.C:
			read()
		}
	}
}

func (f *followedLogFile) read(emit func(string)) {
	info, err := os.Stat(f.path)
	if err != nil {
		return
	}

	// A reload normally appends to the same files, but reset if a runtime or a
	// user replaces or truncates one of them.
	if f.info != nil && (!os.SameFile(f.info, info) || info.Size() < f.offset) {
		f.offset = 0
		f.pending = nil
	}
	f.info = info

	if info.Size() == f.offset {
		return
	}

	file, err := os.Open(f.path)
	if err != nil {
		return
	}
	defer file.Close()

	if _, err := file.Seek(f.offset, io.SeekStart); err != nil {
		return
	}

	appended, err := io.ReadAll(file)
	if err != nil || len(appended) == 0 {
		return
	}
	f.offset += int64(len(appended))
	f.pending = append(f.pending, appended...)

	for {
		newline := bytes.IndexByte(f.pending, '\n')
		if newline < 0 {
			return
		}

		line := bytes.TrimSuffix(f.pending[:newline], []byte{'\r'})
		emit(string(line))
		f.pending = f.pending[newline+1:]
	}
}
