package archive

import (
	"archive/tar"
	"compress/gzip"
	"io"
	"os"
	"path"
	"path/filepath"
	"testing"
)

// TestReplaceKeepsTheRestOfTheBundle covers the hot-swap path: the sources are
// swapped into the built bundle, and every other entry must come through as
// built. A Python build links its virtual environment's interpreter into the
// image, and a lost mode makes an executable helper script non-executable;
// either way the container fails to start. A replaced source keeps its built
// mode for the same reason, since a build step may have made it executable.
func TestReplaceKeepsTheRestOfTheBundle(t *testing.T) {
	bundle := filepath.Join(t.TempDir(), "build.tar.gz")
	file, err := os.Create(bundle)
	if err != nil {
		t.Fatal(err)
	}
	compressed := gzip.NewWriter(file)
	writer := tar.NewWriter(compressed)
	for _, entry := range []struct {
		header   tar.Header
		contents string
	}{
		{tar.Header{Name: "./runtime-env/bin/python", Typeflag: tar.TypeSymlink, Linkname: "/usr/local/bin/python3"}, ""},
		{tar.Header{Name: "./bin/run.sh", Typeflag: tar.TypeReg, Mode: 0o755, Size: 10}, "#!/bin/sh\n"},
		{tar.Header{Name: "./src/main.py", Typeflag: tar.TypeReg, Mode: 0o644, Size: 3}, "old"},
		{tar.Header{Name: "./src/start.sh", Typeflag: tar.TypeReg, Mode: 0o755, Size: 3}, "old"},
	} {
		if err := writer.WriteHeader(&entry.header); err != nil {
			t.Fatal(err)
		}
		if _, err := writer.Write([]byte(entry.contents)); err != nil {
			t.Fatal(err)
		}
	}
	if err := writer.Close(); err != nil {
		t.Fatal(err)
	}
	if err := compressed.Close(); err != nil {
		t.Fatal(err)
	}
	if err := file.Close(); err != nil {
		t.Fatal(err)
	}

	source := t.TempDir()
	writeFile(t, filepath.Join(source, "src", "main.py"), "new", 0o644)
	writeFile(t, filepath.Join(source, "src", "start.sh"), "new", 0o644)

	if err := ReplaceTarGzFiles(bundle, source, []string{"src/main.py", "src/start.sh"}); err != nil {
		t.Fatal(err)
	}

	file, err = os.Open(bundle)
	if err != nil {
		t.Fatal(err)
	}
	defer file.Close()
	decompressed, err := gzip.NewReader(file)
	if err != nil {
		t.Fatal(err)
	}
	headers := map[string]*tar.Header{}
	contents := map[string]string{}
	reader := tar.NewReader(decompressed)
	for {
		header, err := reader.Next()
		if err == io.EOF {
			break
		}
		if err != nil {
			t.Fatal(err)
		}
		body, err := io.ReadAll(reader)
		if err != nil {
			t.Fatal(err)
		}
		name := path.Clean(header.Name)
		if _, ok := headers[name]; ok {
			t.Errorf("%s is in the bundle twice", name)
		}
		headers[name] = header
		contents[name] = string(body)
	}

	if header := headers["runtime-env/bin/python"]; header == nil || header.Typeflag != tar.TypeSymlink || header.Linkname != "/usr/local/bin/python3" {
		t.Errorf("runtime-env/bin/python = %+v, want a symlink to /usr/local/bin/python3", header)
	}
	if header := headers["bin/run.sh"]; header == nil || header.Mode != 0o755 {
		t.Errorf("bin/run.sh = %+v, want mode 0755", header)
	}
	if contents["src/main.py"] != "new" {
		t.Errorf("src/main.py = %q, want %q", contents["src/main.py"], "new")
	}
	if header := headers["src/start.sh"]; header == nil || header.Mode != 0o755 || contents["src/start.sh"] != "new" {
		t.Errorf("src/start.sh = %+v with %q, want mode 0755 with %q", header, contents["src/start.sh"], "new")
	}
}

// TestExtractRefusesPathTraversal pins the guard on archive entry names. The
// archive is the user's own deployment, but it is still untrusted input.
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
