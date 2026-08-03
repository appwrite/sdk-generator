package archive

import (
	"os"
	"path/filepath"
	"runtime"
	"testing"
)

// TestTarRoundTripPreservesContentAndMode covers the hot-swap path: unpack the
// bundle, overlay sources, repack. A mode lost here makes an executable helper
// script inside the bundle non-executable, and the container fails to start.
func TestTarRoundTripPreservesContentAndMode(t *testing.T) {
	source := t.TempDir()
	writeFile(t, filepath.Join(source, "a.txt"), "hello", 0o644)
	writeFile(t, filepath.Join(source, "bin", "run.sh"), "#!/bin/sh\n", 0o755)

	archive := filepath.Join(t.TempDir(), "build.tar.gz")
	if err := CreateTarGz(archive, source); err != nil {
		t.Fatal(err)
	}

	destination := t.TempDir()
	if err := ExtractTarGz(archive, destination); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(filepath.Join(destination, "a.txt"))
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != "hello" {
		t.Errorf("a.txt = %q, want %q", contents, "hello")
	}

	info, err := os.Stat(filepath.Join(destination, "bin", "run.sh"))
	if err != nil {
		t.Fatal(err)
	}
	// Windows has no Unix permission bits -- it reports 0666 for every file --
	// so the executable bit is only meaningful, and only assertable, elsewhere.
	// The round-trip above still runs there, which is the part that could break
	// on a path separator.
	if runtime.GOOS != "windows" && info.Mode().Perm() != 0o755 {
		t.Errorf("run.sh mode = %v, want 0755", info.Mode().Perm())
	}
}

// TestExtractRefusesPathTraversal pins the guard on archive entry names. The
// bundle is the user's own build output, but it is still untrusted input.
func TestExtractRefusesPathTraversal(t *testing.T) {
	destination := t.TempDir()

	if _, err := SafeJoin(destination, "../escaped.txt"); err == nil {
		t.Error("../escaped.txt should be refused")
	}
	if _, err := SafeJoin(destination, "a/../../escaped.txt"); err == nil {
		t.Error("a/../../escaped.txt should be refused")
	}
	if _, err := SafeJoin(destination, "a/b.txt"); err != nil {
		t.Errorf("a/b.txt should be allowed: %v", err)
	}
}

func writeFile(t *testing.T, path, contents string, mode os.FileMode) {
	t.Helper()

	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte(contents), mode); err != nil {
		t.Fatal(err)
	}
}
