package docker

import (
	"context"
	"os"
	"path/filepath"
	"reflect"
	"strings"
	"testing"
	"time"
)

func TestFollowedLogFileEmitsAppendedLinesOnce(t *testing.T) {
	path := filepath.Join(t.TempDir(), "logs.txt")
	if err := os.WriteFile(path, nil, 0o600); err != nil {
		t.Fatal(err)
	}

	file := &followedLogFile{path: path}
	var lines []string
	emit := func(line string) { lines = append(lines, line) }

	appendFile(t, path, "first\nsecond\r\npartial")
	file.read(emit)
	file.read(emit)
	appendFile(t, path, " line\n")
	file.read(emit)

	want := []string{"first", "second", "partial line"}
	if !reflect.DeepEqual(lines, want) {
		t.Fatalf("lines = %#v, want %#v", lines, want)
	}
}

func TestFollowedLogFileResetsAfterTruncateOrReplace(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "logs.txt")
	if err := os.WriteFile(path, []byte("before\n"), 0o600); err != nil {
		t.Fatal(err)
	}

	file := &followedLogFile{path: path}
	var lines []string
	emit := func(line string) { lines = append(lines, line) }
	file.read(emit)

	if err := os.WriteFile(path, []byte("short\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	file.read(emit)

	replacement := filepath.Join(directory, "replacement.txt")
	if err := os.WriteFile(replacement, []byte("replacement\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	if err := os.Remove(path); err != nil {
		t.Fatal(err)
	}
	if err := os.Rename(replacement, path); err != nil {
		t.Fatal(err)
	}
	file.read(emit)

	want := []string{"before", "short", "replacement"}
	if !reflect.DeepEqual(lines, want) {
		t.Fatalf("lines = %#v, want %#v", lines, want)
	}
}

func TestFollowedLogFileDetectsTruncateAndRegrowBetweenReads(t *testing.T) {
	path := filepath.Join(t.TempDir(), "logs.txt")
	if err := os.WriteFile(path, []byte("old first\nold second\n"), 0o600); err != nil {
		t.Fatal(err)
	}

	file := &followedLogFile{path: path}
	var lines []string
	emit := func(line string) { lines = append(lines, line) }
	file.read(emit)

	// This replacement is larger than the old offset. A size-only follower
	// mistakes it for an append and starts in the middle of "new second".
	regrown := []byte("new first\nnew second\nnew third\n")
	if err := os.WriteFile(path, regrown, 0o600); err != nil {
		t.Fatal(err)
	}
	file.read(emit)

	want := []string{"old first", "old second", "new first", "new second", "new third"}
	if !reflect.DeepEqual(lines, want) {
		t.Fatalf("lines = %#v, want %#v", lines, want)
	}
}

func TestFollowedLogFileChecksTheWholePrefixAfterRegrowth(t *testing.T) {
	path := filepath.Join(t.TempDir(), "logs.txt")
	sharedTail := strings.Repeat("x", 256)
	oldLine := strings.Repeat("a", 300) + sharedTail
	if err := os.WriteFile(path, []byte(oldLine+"\n"), 0o600); err != nil {
		t.Fatal(err)
	}

	file := &followedLogFile{path: path}
	var lines []string
	emit := func(line string) { lines = append(lines, line) }
	file.read(emit)

	// The final 256 bytes before the old offset are unchanged, but the prefix
	// before them belongs to the new runtime and must not be skipped.
	newLine := strings.Repeat("b", 300) + sharedTail
	if err := os.WriteFile(path, []byte(newLine+"\nnew suffix\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	file.read(emit)

	want := []string{oldLine, newLine, "new suffix"}
	if !reflect.DeepEqual(lines, want) {
		t.Fatalf("lines = %#v, want %#v", lines, want)
	}
}

func TestFollowRuntimeLogsStreamsStdoutAndStderr(t *testing.T) {
	directory := t.TempDir()
	scratch := filepath.Join(directory, AppwriteDirectory)
	if err := os.MkdirAll(scratch, 0o700); err != nil {
		t.Fatal(err)
	}

	stdout := filepath.Join(scratch, "logs.txt")
	stderr := filepath.Join(scratch, "errors.txt")
	for _, path := range []string{stdout, stderr} {
		if err := os.WriteFile(path, nil, 0o600); err != nil {
			t.Fatal(err)
		}
	}

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	lines := make(chan string, 2)
	go FollowRuntimeLogs(ctx, directory, func(line string) { lines <- line })

	appendFile(t, stdout, "from log\n")
	appendFile(t, stderr, "from error\n")

	for _, want := range []string{"from log", "from error"} {
		select {
		case got := <-lines:
			if got != want {
				t.Fatalf("line = %q, want %q", got, want)
			}
		case <-time.After(time.Second):
			t.Fatalf("timed out waiting for %q", want)
		}
	}
}

func appendFile(t *testing.T, path, contents string) {
	t.Helper()

	file, err := os.OpenFile(path, os.O_APPEND|os.O_WRONLY, 0)
	if err != nil {
		t.Fatal(err)
	}
	defer file.Close()

	if _, err := file.WriteString(contents); err != nil {
		t.Fatal(err)
	}
}
