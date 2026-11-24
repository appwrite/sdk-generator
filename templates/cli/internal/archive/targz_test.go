package archive

import (
	"archive/tar"
	"compress/gzip"
	"io"
	"os"
	"path/filepath"
	"runtime"
	"testing"
)

// TestReplaceKeepsTheBuildAroundTheSwappedSources covers the hot-swap path:
// the sources are swapped into the bundle and everything else the build made
// survives. A dropped venv symlink makes Python start on the system
// interpreter, and a lost mode makes an executable helper non-executable.
func TestReplaceKeepsTheBuildAroundTheSwappedSources(t *testing.T) {
	bundle := filepath.Join(t.TempDir(), "build.tar.gz")
	writeArchive(t, bundle, []tar.Header{
		{Name: "./runtime-env/bin/python3", Typeflag: tar.TypeSymlink, Linkname: "/usr/local/bin/python3", Mode: 0o777},
		{Name: "./bin/run.sh", Typeflag: tar.TypeReg, Mode: 0o755, Size: int64(len("#!/bin/sh\n"))},
		{Name: "./src/main.py", Typeflag: tar.TypeReg, Mode: 0o644, Size: int64(len("v1"))},
	}, []string{"", "#!/bin/sh\n", "v1"})

	source := t.TempDir()
	writeFile(t, filepath.Join(source, "src", "main.py"), "v2", 0o644)

	if err := ReplaceTarGz(bundle, source, []string{"src/main.py"}); err != nil {
		t.Fatal(err)
	}

	entries := readArchive(t, bundle)

	link, ok := entries["runtime-env/bin/python3"]
	if !ok || link.Typeflag != tar.TypeSymlink || link.Linkname != "/usr/local/bin/python3" {
		t.Errorf("runtime-env/bin/python3 = %+v, want a symlink to /usr/local/bin/python3", link)
	}
	if mode := entries["bin/run.sh"].Mode; mode != 0o755 {
		t.Errorf("bin/run.sh mode = %o, want 755", mode)
	}

	destination := t.TempDir()
	if err := ExtractTarGz(bundle, destination); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(filepath.Join(destination, "src", "main.py"))
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != "v2" {
		t.Errorf("src/main.py = %q, want %q", contents, "v2")
	}

	info, err := os.Stat(filepath.Join(destination, "bin", "run.sh"))
	if err != nil {
		t.Fatal(err)
	}
	// Windows has no Unix permission bits -- it reports 0666 for every file --
	// so the executable bit is only meaningful, and only assertable, elsewhere.
	if runtime.GOOS != "windows" && info.Mode().Perm() != 0o755 {
		t.Errorf("extracted run.sh mode = %v, want 0755", info.Mode().Perm())
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

func writeArchive(t *testing.T, path string, headers []tar.Header, contents []string) {
	t.Helper()

	file, err := os.Create(path)
	if err != nil {
		t.Fatal(err)
	}
	defer file.Close()

	compressed := gzip.NewWriter(file)
	writer := tar.NewWriter(compressed)
	for index, header := range headers {
		if err := writer.WriteHeader(&header); err != nil {
			t.Fatal(err)
		}
		if _, err := writer.Write([]byte(contents[index])); err != nil {
			t.Fatal(err)
		}
	}
	if err := writer.Close(); err != nil {
		t.Fatal(err)
	}
	if err := compressed.Close(); err != nil {
		t.Fatal(err)
	}
}

// readArchive returns every entry by its normalised name, failing on a
// duplicate: a swapped file must replace its old entry, not shadow it.
func readArchive(t *testing.T, path string) map[string]tar.Header {
	t.Helper()

	file, err := os.Open(path)
	if err != nil {
		t.Fatal(err)
	}
	defer file.Close()

	compressed, err := gzip.NewReader(file)
	if err != nil {
		t.Fatal(err)
	}

	entries := map[string]tar.Header{}
	reader := tar.NewReader(compressed)
	for {
		header, err := reader.Next()
		if err == io.EOF {
			return entries
		}
		if err != nil {
			t.Fatal(err)
		}
		name := entryName(header.Name)
		if _, duplicate := entries[name]; duplicate {
			t.Errorf("%s appears twice in the archive", name)
		}
		entries[name] = *header
	}
}
