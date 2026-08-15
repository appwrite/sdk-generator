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
const (
	runtimeLogPollInterval = 50 * time.Millisecond
	runtimeLogBookmarkSize = 256
)

// followedLogFile remembers how much of one runtime log has been emitted. The
// bookmark detects a truncate-and-regrow that happens entirely between polls.
type followedLogFile struct {
	path           string
	info           os.FileInfo
	offset         int64
	pending        []byte
	bookmark       []byte
	bookmarkOffset int64
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
	if !reset && f.info != nil && info.Size() == f.offset {
		// The file changed without growing, so it was rewritten in place.
		reset = true
	}
	if !reset && f.offset > 0 {
		reset = !f.bookmarkMatches(file)
	}
	if reset {
		f.offset = 0
		f.pending = nil
		f.bookmark = nil
		f.bookmarkOffset = 0
	}

	if _, err := file.Seek(f.offset, io.SeekStart); err != nil {
		return
	}

	appended, err := io.ReadAll(file)
	if err != nil || len(appended) == 0 {
		return
	}
	f.offset += int64(len(appended))
	f.refreshBookmark(appended)
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

func (f *followedLogFile) bookmarkMatches(file *os.File) bool {
	if len(f.bookmark) == 0 {
		return true
	}

	actual := make([]byte, len(f.bookmark))
	if _, err := file.ReadAt(actual, f.bookmarkOffset); err != nil {
		return false
	}

	return bytes.Equal(actual, f.bookmark)
}

func (f *followedLogFile) refreshBookmark(appended []byte) {
	if len(appended) >= runtimeLogBookmarkSize {
		f.bookmark = append(f.bookmark[:0], appended[len(appended)-runtimeLogBookmarkSize:]...)
	} else {
		keep := min(len(f.bookmark), runtimeLogBookmarkSize-len(appended))
		bookmark := make([]byte, 0, keep+len(appended))
		bookmark = append(bookmark, f.bookmark[len(f.bookmark)-keep:]...)
		f.bookmark = append(bookmark, appended...)
	}

	f.bookmarkOffset = f.offset - int64(len(f.bookmark))
}
