package watch

import (
	"os"
	"path/filepath"
	"testing"
	"time"
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

func assertNoChange(t *testing.T, changes <-chan string, operation string) {
	t.Helper()

	select {
	case changed := <-changes:
		t.Fatalf("%s reported %q as changed", operation, changed)
	case <-time.After(500 * time.Millisecond):
	}
}
