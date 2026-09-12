package watch

import (
	"os"
	"path/filepath"
	"strings"
	"sync"
	"testing"
	"time"
)

func TestWatcherIgnoresMetadataOnlyChanges(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "main.ts")
	if err := os.WriteFile(path, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	changes, watcher := watch(t, directory)

	future := time.Now().Add(time.Minute)
	if err := os.Chtimes(path, future, future); err != nil {
		t.Fatal(err)
	}
	assertNoChange(t, changes, "timestamp update")

	if err := os.WriteFile(path, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}
	assertNoChange(t, changes, "same-content rewrite")

	if err := os.WriteFile(path, []byte("export const value = 2;\n"), 0o644); err != nil {
		t.Fatal(err)
	}
	assertChange(t, changes, "main.ts", "content update")

	if err := watcher.Close(); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte("export const value = 3;\n"), 0o644); err != nil {
		t.Fatal(err)
	}
	assertNoChange(t, changes, "edit after close")
}

// An editor saving a file commonly truncates it and writes the bytes back, so
// the file is briefly empty. That must not be reported as an edit, while a file
// the user genuinely emptied must be.
func TestWatcherIgnoresTruncateAndRewrite(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "main.ts")
	contents := []byte("export const value = 1;\n")
	if err := os.WriteFile(path, contents, 0o644); err != nil {
		t.Fatal(err)
	}

	changes, _ := watch(t, directory)

	save(t, path, contents, 5*time.Millisecond)
	assertNoChange(t, changes, "truncate and rewrite")

	if err := os.WriteFile(path, nil, 0o644); err != nil {
		t.Fatal(err)
	}
	assertChange(t, changes, "main.ts", "emptied file")
}

// Close returns only once no callback can still be running. A caller tearing
// down the reload queue its callback writes to would otherwise race a report
// already in flight.
func TestWatcherReportsNothingAfterClose(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "main.ts")
	if err := os.WriteFile(path, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	var mutex sync.Mutex
	reporting := false
	started := make(chan struct{})
	watcher, err := Start(directory, func(string) bool { return false }, func(string) {
		mutex.Lock()
		reporting = true
		mutex.Unlock()
		close(started)

		// Stands in for the work a real callback does between being handed a
		// path and returning.
		time.Sleep(time.Second)

		mutex.Lock()
		reporting = false
		mutex.Unlock()
	})
	if err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = watcher.Close() })

	if err := os.WriteFile(path, []byte("export const value = 2;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	select {
	case <-started:
	case <-time.After(5 * time.Second):
		t.Fatal("change was never reported")
	}

	if err := watcher.Close(); err != nil {
		t.Fatal(err)
	}

	mutex.Lock()
	defer mutex.Unlock()
	if reporting {
		t.Fatal("close returned while a change was still being reported")
	}
}

// An edit must be reported even though the tree around it never falls quiet.
func TestWatcherReportsEditsWhileTreeIsBusy(t *testing.T) {
	directory := t.TempDir()
	edited := filepath.Join(directory, "main.ts")
	if err := os.WriteFile(edited, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	changes, _ := watch(t, directory)
	busy(t, filepath.Join(directory, "build.log"))

	if err := os.WriteFile(edited, []byte("export const value = 2;\n"), 0o644); err != nil {
		t.Fatal(err)
	}
	awaitChange(t, changes, "main.ts", "edit in a busy tree")
}

// A file written continuously never falls quiet, so it has to be reported on
// some bound instead. Waiting for it to settle would mean never reloading it.
func TestWatcherReportsFileThatNeverSettles(t *testing.T) {
	directory := t.TempDir()
	written := filepath.Join(directory, "main.ts")
	if err := os.WriteFile(written, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	changes, _ := watch(t, directory)
	busy(t, written)

	awaitChange(t, changes, "main.ts", "continuously written file")
}

// Saving a file without changing it must stay silent however busy the rest of
// the tree is: a save is still a truncate and a rewrite, and the quiet interval
// one path needs cannot be spent by another path's activity.
//
// The saves are spaced so they drift against any batching the watcher does
// rather than landing at the same point in it each time, since a save is only
// ever ignored if it is ignored whenever it happens.
func TestWatcherIgnoresUnchangedSavesWhileTreeIsBusy(t *testing.T) {
	directory := t.TempDir()
	saved := filepath.Join(directory, "main.ts")
	contents := []byte("export const value = 1;\n")
	if err := os.WriteFile(saved, contents, 0o644); err != nil {
		t.Fatal(err)
	}

	changes, _ := watch(t, directory)
	busy(t, filepath.Join(directory, "build.log"))

	// A slow writer widens the window in which the file is empty, so a save
	// that is not left to settle is caught rather than merely made likely.
	for range 40 {
		save(t, saved, contents, 60*time.Millisecond)
		time.Sleep(130 * time.Millisecond)
	}
	time.Sleep(2 * time.Second)

	for {
		select {
		case changed := <-changes:
			if changed == "main.ts" {
				t.Fatal("unchanged save was reported")
			}
		default:
			return
		}
	}
}

// save writes a file the way an editor does, truncating it before restoring the
// bytes so it is briefly empty on disk. The gap stands in for however long the
// writer takes over its bytes.
func save(t *testing.T, path string, contents []byte, gap time.Duration) {
	t.Helper()

	truncated, err := os.OpenFile(path, os.O_WRONLY|os.O_TRUNC, 0o644)
	if err != nil {
		t.Fatal(err)
	}
	if err := truncated.Close(); err != nil {
		t.Fatal(err)
	}
	time.Sleep(gap)
	if err := os.WriteFile(path, contents, 0o644); err != nil {
		t.Fatal(err)
	}
}

// busy rewrites a file until the test ends, standing in for a build tool or a
// test runner working in the tree.
func busy(t *testing.T, path string) {
	t.Helper()

	stop := make(chan struct{})
	stopped := make(chan struct{})
	t.Cleanup(func() {
		close(stop)
		<-stopped
	})

	go func() {
		defer close(stopped)
		for line := 0; ; line++ {
			select {
			case <-stop:
				return
			default:
			}
			_ = os.WriteFile(path, []byte(strings.Repeat("x", line%64+1)), 0o644)
			time.Sleep(5 * time.Millisecond)
		}
	}()
}

func watch(t *testing.T, directory string) (<-chan string, *Watcher) {
	t.Helper()

	changes := make(chan string, 64)
	watcher, err := Start(directory, func(string) bool { return false }, func(path string) {
		changes <- path
	})
	if err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = watcher.Close() })

	return changes, watcher
}

func assertChange(t *testing.T, changes <-chan string, want string, operation string) {
	t.Helper()

	select {
	case changed := <-changes:
		if changed != want {
			t.Fatalf("%s reported %q, want %q", operation, changed, want)
		}
	case <-time.After(5 * time.Second):
		t.Fatalf("%s was not reported", operation)
	}
}

// awaitChange waits for one path among reports about others.
func awaitChange(t *testing.T, changes <-chan string, want string, operation string) {
	t.Helper()

	deadline := time.After(5 * time.Second)
	for {
		select {
		case changed := <-changes:
			if changed == want {
				return
			}
		case <-deadline:
			t.Fatalf("%s was not reported", operation)
		}
	}
}

func assertNoChange(t *testing.T, changes <-chan string, operation string) {
	t.Helper()

	select {
	case changed := <-changes:
		t.Fatalf("%s reported %q as changed", operation, changed)
	case <-time.After(500 * time.Millisecond):
	}
}
