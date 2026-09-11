package watch

import (
	"crypto/sha256"
	"os"
	"path/filepath"
	"testing"
	"testing/synctest"
	"time"

	"github.com/fsnotify/fsnotify"
)

func TestWatcherIgnoresMetadataOnlyChanges(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "main.ts")
	if err := os.WriteFile(path, []byte("export const value = 1;\n"), 0o644); err != nil {
		t.Fatal(err)
	}

	changes := make(chan string, 2)
	watcher, err := Start(directory, func(string) bool { return false }, func(path string) {
		changes <- path
	})
	if err != nil {
		t.Fatal(err)
	}
	defer watcher.Close()

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

	select {
	case changed := <-changes:
		if changed != "main.ts" {
			t.Fatalf("changed path = %q, want main.ts", changed)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("content update was not reported")
	}
}

// A synthetic event source and virtual clock force the truncate notification
// to arrive before the rewrite, without relying on OS scheduling to hit the race.
func TestWatcherSettlesTruncateAndRewrite(t *testing.T) {
	synctest.Test(t, func(t *testing.T) {
		directory := t.TempDir()
		path := filepath.Join(directory, "main.ts")
		contents := []byte("export const value = 1;\n")
		if err := os.WriteFile(path, contents, 0o644); err != nil {
			t.Fatal(err)
		}
		initial, err := fingerprint(path)
		if err != nil {
			t.Fatal(err)
		}
		watcher := &Watcher{
			watcher: &fsnotify.Watcher{
				Events: make(chan fsnotify.Event),
				Errors: make(chan error),
			},
			root:         directory,
			ignored:      func(string) bool { return false },
			fingerprints: map[string][sha256.Size]byte{"main.ts": initial},
			done:         make(chan struct{}),
		}
		changes := make(chan string, 4)
		go watcher.run(func(path string) { changes <- path })
		defer close(watcher.done)

		write := func(contents []byte) {
			t.Helper()
			if err := os.WriteFile(path, contents, 0o644); err != nil {
				t.Fatal(err)
			}
			watcher.watcher.Events <- fsnotify.Event{Name: path, Op: fsnotify.Write}
		}
		write(nil)
		time.Sleep(settleDelay / 2)
		write(contents)
		time.Sleep(settleDelay)
		synctest.Wait()
		select {
		case path := <-changes:
			t.Fatalf("same-content rewrite reported %q", path)
		default:
		}

		// An intentionally empty file must still be reported after it settles.
		write(nil)
		time.Sleep(settleDelay)
		synctest.Wait()
		select {
		case path := <-changes:
			if path != "main.ts" {
				t.Fatalf("changed path = %q", path)
			}
		default:
			t.Fatal("empty content update was not reported")
		}

		// Closing with an outstanding debounce must not call the callback.
		write(contents)
		close(watcher.watcher.Events)
		synctest.Wait()
		time.Sleep(settleDelay)
		synctest.Wait()
		select {
		case path := <-changes:
			t.Fatalf("closed watcher reported %q", path)
		default:
		}
	})
}

func assertNoChange(t *testing.T, changes <-chan string, operation string) {
	t.Helper()

	select {
	case changed := <-changes:
		t.Fatalf("%s reported %q as changed", operation, changed)
	case <-time.After(500 * time.Millisecond):
	}
}
