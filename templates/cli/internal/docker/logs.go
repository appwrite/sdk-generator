package docker

import (
	"bytes"
	"context"
	"crypto/sha256"
	"hash"
	"io"
	"os"
	"path/filepath"
	"time"
)

// runtimeLogPollInterval bounds how long a function log waits before it is
// shown. Polling keeps this portable and avoids holding the files open while
// cleanup removes .appwrite on Windows.
const runtimeLogPollInterval = 50 * time.Millisecond

// followedLogFile remembers how much of one runtime log has been emitted. The
// digest detects a truncate-and-regrow that happens entirely between polls.
type followedLogFile struct {
	path    string
	info    os.FileInfo
	offset  int64
	pending []byte
	digest  [sha256.Size]byte
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
	if f.info != nil && info.Size() == f.offset &&
		os.SameFile(f.info, info) && info.ModTime().Equal(f.info.ModTime()) {
		return
	}

	file, err := os.Open(f.path)
	if err != nil {
		return
	}
	defer file.Close()

	reset := f.info != nil && (!os.SameFile(f.info, info) || info.Size() < f.offset)
	hasher := sha256.New()
	if !reset && f.offset > 0 {
		var matches bool
		hasher, matches = f.prefixMatches(file)
		reset = !matches
	}
	if reset {
		f.offset = 0
		f.pending = nil
		hasher = sha256.New()
		if _, err := file.Seek(0, io.SeekStart); err != nil {
			return
		}
	}

	appended, err := io.ReadAll(file)
	if err != nil {
		return
	}
	if len(appended) == 0 {
		if latest, err := file.Stat(); err == nil {
			f.info = latest
		} else {
			f.info = info
		}
		return
	}
	f.offset += int64(len(appended))
	_, _ = hasher.Write(appended)
	copy(f.digest[:], hasher.Sum(nil))
	f.pending = append(f.pending, appended...)
	if latest, err := file.Stat(); err == nil {
		f.info = latest
	} else {
		f.info = info
	}

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

// prefixMatches verifies every byte already emitted and leaves the hasher and
// file positioned at the old offset. A bounded tail sample is insufficient: a
// restarted runtime can reproduce that sample after replacing an earlier line.
func (f *followedLogFile) prefixMatches(file *os.File) (hash.Hash, bool) {
	hasher := sha256.New()
	copied, err := io.CopyN(hasher, file, f.offset)
	if err != nil || copied != f.offset {
		return hasher, false
	}

	return hasher, bytes.Equal(hasher.Sum(nil), f.digest[:])
}
