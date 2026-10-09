package expo

import (
	"archive/tar"
	"bytes"
	"compress/gzip"
	"context"
	"crypto/sha256"
	"encoding/hex"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"sync/atomic"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

func TestNodeBuild(t *testing.T) {
	for _, test := range []struct{ goos, goarch, name, extension string }{
		{"darwin", "arm64", "node-v24.21.0-darwin-arm64", ".tar.gz"},
		{"darwin", "amd64", "node-v24.21.0-darwin-x64", ".tar.gz"},
		{"linux", "amd64", "node-v24.21.0-linux-x64", ".tar.gz"},
		{"windows", "arm64", "node-v24.21.0-win-arm64", ".zip"},
	} {
		name, extension, err := nodeBuild(test.goos, test.goarch)
		if err != nil || name != test.name || extension != test.extension {
			t.Errorf("nodeBuild(%s, %s) = %s, %s, %v", test.goos, test.goarch, name, extension, err)
		}
		if _, ok := nodeChecksums[name+extension]; !ok {
			t.Errorf("no checksum for %s%s", name, extension)
		}
	}
	if _, _, err := nodeBuild("plan9", "386"); err == nil {
		t.Error("found a build for plan9/386")
	}
}

type entry struct {
	name     string
	mode     int64
	contents string
	link     string
}

func tarball(t *testing.T, entries []entry) []byte {
	t.Helper()
	var buffer bytes.Buffer
	compressed := gzip.NewWriter(&buffer)
	writer := tar.NewWriter(compressed)
	for _, item := range entries {
		header := &tar.Header{Name: item.name, Mode: item.mode, Size: int64(len(item.contents)), Typeflag: tar.TypeReg}
		if item.link != "" {
			header = &tar.Header{Name: item.name, Mode: 0o777, Linkname: item.link, Typeflag: tar.TypeSymlink}
		}
		if err := writer.WriteHeader(header); err != nil {
			t.Fatal(err)
		}
		if _, err := writer.Write([]byte(item.contents)); err != nil {
			t.Fatal(err)
		}
	}
	if err := writer.Close(); err != nil {
		t.Fatal(err)
	}
	if err := compressed.Close(); err != nil {
		t.Fatal(err)
	}

	return buffer.Bytes()
}

// fakeNodeBuild is a Node.js build whose node is a shell script: it answers
// --version, acts as npm when given npm-cli.js, and otherwise runs as the
// helper, writing a key to the --out file.
func fakeNodeBuild(t *testing.T) (string, []byte) {
	t.Helper()
	if runtime.GOOS == "windows" {
		t.Skip("shell stand-ins")
	}
	name, _, err := nodeBuild(runtime.GOOS, runtime.GOARCH)
	if err != nil {
		t.Skip(err)
	}
	node := `#!/bin/sh
if [ "$1" = "--version" ]; then echo "v24.21.0"; exit 0; fi
case "$1" in
  *npm-cli.js) mkdir -p node_modules/@expo/apple-utils && echo '{}' > node_modules/@expo/apple-utils/package.json; exit 0 ;;
esac
while [ $# -gt 0 ]; do
  if [ "$1" = "--out" ]; then printf '%s' '{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}' > "$2"; fi
  shift
done
`

	return name, tarball(t, []entry{
		{name: name + "/bin/node", mode: 0o755, contents: node},
		{name: name + "/lib/node_modules/npm/bin/npm-cli.js", mode: 0o644, contents: "// npm"},
		{name: name + "/bin/npm", link: "../lib/node_modules/npm/bin/npm-cli.js"},
	})
}

// serve answers the Node.js download, counting requests, and makes archive
// the expected build for this machine.
func serve(t *testing.T, name string, archive []byte) (*httptest.Server, *atomic.Int32) {
	t.Helper()
	sum := sha256.Sum256(archive)
	previous := nodeChecksums[name+".tar.gz"]
	nodeChecksums[name+".tar.gz"] = hex.EncodeToString(sum[:])
	t.Cleanup(func() { nodeChecksums[name+".tar.gz"] = previous })

	requests := &atomic.Int32{}
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		requests.Add(1)
		if r.URL.Path != "/"+nodeRelease+"/"+name+".tar.gz" {
			w.WriteHeader(http.StatusNotFound)

			return
		}
		_, _ = w.Write(archive)
	}))
	t.Cleanup(server.Close)

	return server, requests
}

// withoutNode leaves only mkdir on PATH, so neither node nor npm is found.
func withoutNode(t *testing.T) {
	t.Helper()
	bin := t.TempDir()
	if err := os.Symlink("/bin/mkdir", filepath.Join(bin, "mkdir")); err != nil {
		t.Fatal(err)
	}
	t.Setenv("PATH", bin)
}

func TestCreateKeyDownloadsNodeWhenMissing(t *testing.T) {
	name, archive := fakeNodeBuild(t)
	server, requests := serve(t, name, archive)
	withoutNode(t)

	adapter := &Adapter{NodeDist: server.URL, HTTP: server.Client(), Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	var logged []string
	request := apns.Request{
		TeamID: "ABCDE12345", Name: "Appwrite Push", Environment: apns.EnvironmentAll, SessionDir: t.TempDir(),
		Log: func(format string, args ...any) { logged = append(logged, format) },
	}

	for range 2 {
		key, err := adapter.CreateKey(context.Background(), request)
		if err != nil || key.KeyID != "KEY1234567" {
			t.Fatalf("key = %+v, %v", key, err)
		}
	}
	if requests.Load() != 1 {
		t.Errorf("downloaded Node.js %d times", requests.Load())
	}
	if len(logged) != 1 || !strings.Contains(logged[0], "Downloading Node.js %s") {
		t.Errorf("logged %v", logged)
	}
	root := filepath.Join(request.SessionDir, "runtime", name)
	if info, err := os.Stat(filepath.Join(root, "bin", "node")); err != nil || info.Mode().Perm()&0o100 == 0 {
		t.Errorf("node = %v, %v", info, err)
	}
	if _, err := os.Lstat(filepath.Join(root, "bin", "npm")); err == nil {
		t.Error("unpacked a symbolic link")
	}
}

func TestDownloadNodeRejectsABadChecksum(t *testing.T) {
	name, archive := fakeNodeBuild(t)
	server, _ := serve(t, name, archive)
	nodeChecksums[name+".tar.gz"] = strings.Repeat("0", 64)
	withoutNode(t)

	adapter := &Adapter{NodeDist: server.URL, HTTP: server.Client()}
	dir := t.TempDir()
	_, err := adapter.CreateKey(context.Background(), apns.Request{Name: "Appwrite Push", Environment: apns.EnvironmentAll, SessionDir: dir})
	if err == nil || !strings.Contains(err.Error(), "does not match its checksum") {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(filepath.Join(dir, "runtime", name)); err == nil {
		t.Error("unpacked a download that failed its checksum")
	}
}

func TestUntarRefusesPathsOutsideTheFolder(t *testing.T) {
	parent := t.TempDir()
	root := filepath.Join(parent, "root")
	archive := filepath.Join(parent, "evil.tar.gz")
	if err := os.WriteFile(archive, tarball(t, []entry{
		{name: "../evil", mode: 0o644, contents: "x"},
	}), 0o600); err != nil {
		t.Fatal(err)
	}

	if err := untar(archive, root); err == nil || !strings.Contains(err.Error(), "unsafe path") {
		t.Errorf("err = %v", err)
	}
	if _, err := os.Stat(filepath.Join(parent, "evil")); err == nil {
		t.Error("wrote outside the folder")
	}
}
