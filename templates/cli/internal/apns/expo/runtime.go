package expo

import (
	"archive/tar"
	"archive/zip"
	"compress/gzip"
	"context"
	"crypto/sha256"
	"encoding/hex"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"time"
)

// When the machine has no Node.js the expo setup can use, it downloads this
// release, the Active LTS, into the session folder. Nothing is installed
// system-wide. Each build is checked against its SHA-256 from
// https://nodejs.org/dist/v24.21.0/SHASUMS256.txt before it is unpacked.
const nodeRelease = "v24.21.0"

var nodeChecksums = map[string]string{
	"node-v24.21.0-darwin-arm64.tar.gz": "bed7eea5325e1108f32ce5228ddd6a5f0f08a499ee42aa7442aea583702f6057",
	"node-v24.21.0-darwin-x64.tar.gz":   "1462cb3b3046b815cf8ea436d3da450ec1a9f11dac7e5a46b0ada5305d7e8097",
	"node-v24.21.0-linux-arm64.tar.gz":  "724282c3b43aec998aa9527380465b45d229e021b58035f5f4f63095eabfe5d5",
	"node-v24.21.0-linux-x64.tar.gz":    "6e1db87ef58b8819e5d5402eff1536491b18edd8eb7bee5ef7897876e88dc5ff",
	"node-v24.21.0-win-arm64.zip":       "8779b1bde1d39f8d420e3b57aa657b39891af434d3de44a919044cec06785921",
	"node-v24.21.0-win-x64.zip":         "158f7685b44de51f6c0df1d153526cbcd3e1bc739a8dfc607721cef75de9e541",
}

const defaultNodeDist = "https://nodejs.org/dist"

// nodeBuild names the official build for goos/goarch, without its extension,
// and the archive format it comes in.
func nodeBuild(goos, goarch string) (string, string, error) {
	platform := map[string]string{"darwin": "darwin", "linux": "linux", "windows": "win"}[goos]
	arch := map[string]string{"amd64": "x64", "arm64": "arm64"}[goarch]
	if platform == "" || arch == "" {
		return "", "", fmt.Errorf("no Node.js build to download for %s/%s", goos, goarch)
	}
	extension := ".tar.gz"
	if goos == "windows" {
		extension = ".zip"
	}

	return "node-" + nodeRelease + "-" + platform + "-" + arch, extension, nil
}

// nodePaths are the node binary and npm's entry script inside an unpacked
// build. npm is run through node, so its shell wrappers are not needed.
func nodePaths(root, goos string) (string, string) {
	if goos == "windows" {
		return filepath.Join(root, "node.exe"), filepath.Join(root, "node_modules", "npm", "bin", "npm-cli.js")
	}

	return filepath.Join(root, "bin", "node"), filepath.Join(root, "lib", "node_modules", "npm", "bin", "npm-cli.js")
}

// downloadNode makes sure the pinned Node.js is unpacked under dir and returns
// the node binary and npm's entry script.
func (a *Adapter) downloadNode(ctx context.Context, dir string, log func(string, ...any)) (string, string, error) {
	goos, goarch := a.platform()
	name, extension, err := nodeBuild(goos, goarch)
	if err != nil {
		return "", "", err
	}
	root := filepath.Join(dir, name)
	node, npm := nodePaths(root, goos)
	if _, err := os.Stat(npm); err == nil {
		return node, npm, nil
	}
	archive := name + extension
	checksum, ok := nodeChecksums[archive]
	if !ok {
		return "", "", fmt.Errorf("no checksum for %s", archive)
	}

	dist := a.NodeDist
	if dist == "" {
		dist = defaultNodeDist
	}
	log("Node.js %d or later was not found. Downloading Node.js %s (about 50 MB, 200 MB unpacked) into %s ...", minimumNodeMajor, nodeRelease, dir)
	if err := os.MkdirAll(dir, 0o700); err != nil {
		return "", "", err
	}
	downloaded, err := a.fetch(ctx, dist+"/"+nodeRelease+"/"+archive, dir, checksum)
	if err != nil {
		return "", "", err
	}
	defer os.Remove(downloaded)

	// Unpack next to the final folder and move it into place, so a failed or
	// interrupted unpack never looks like a usable runtime.
	staging, err := os.MkdirTemp(dir, ".node-")
	if err != nil {
		return "", "", err
	}
	defer os.RemoveAll(staging)
	if extension == ".zip" {
		err = unzip(downloaded, staging)
	} else {
		err = untar(downloaded, staging)
	}
	if err != nil {
		return "", "", fmt.Errorf("could not unpack %s: %w", archive, err)
	}
	_ = os.RemoveAll(root)
	if err := os.Rename(filepath.Join(staging, name), root); err != nil {
		return "", "", fmt.Errorf("could not unpack %s: %w", archive, err)
	}
	if _, err := os.Stat(npm); err != nil {
		return "", "", fmt.Errorf("%s has no npm", archive)
	}

	return node, npm, nil
}

func (a *Adapter) platform() (string, string) {
	if a.GOOS != "" {
		return a.GOOS, a.GOARCH
	}

	return runtime.GOOS, runtime.GOARCH
}

// fetch downloads url into a file in dir and checks its SHA-256.
func (a *Adapter) fetch(ctx context.Context, url, dir, checksum string) (string, error) {
	client := a.HTTP
	if client == nil {
		client = &http.Client{Timeout: 10 * time.Minute}
	}
	request, err := http.NewRequestWithContext(ctx, http.MethodGet, url, nil)
	if err != nil {
		return "", err
	}
	response, err := client.Do(request)
	if err != nil {
		return "", fmt.Errorf("could not download Node.js: %w", err)
	}
	defer response.Body.Close()
	if response.StatusCode != http.StatusOK {
		return "", fmt.Errorf("could not download Node.js from %s (HTTP %d)", url, response.StatusCode)
	}

	file, err := os.CreateTemp(dir, ".node-download-")
	if err != nil {
		return "", err
	}
	hash := sha256.New()
	_, err = io.Copy(io.MultiWriter(file, hash), response.Body)
	if closeErr := file.Close(); err == nil {
		err = closeErr
	}
	if err != nil {
		os.Remove(file.Name())

		return "", fmt.Errorf("could not download Node.js: %w", err)
	}
	if got := hex.EncodeToString(hash.Sum(nil)); got != checksum {
		os.Remove(file.Name())

		return "", fmt.Errorf("the Node.js download from %s does not match its checksum (got %s)", url, got)
	}

	return file.Name(), nil
}

// inside returns where name unpacks under root, refusing anything that would
// land outside it.
func inside(root, name string) (string, error) {
	clean := filepath.Clean(filepath.FromSlash(name))
	if filepath.IsAbs(clean) || clean == ".." || strings.HasPrefix(clean, ".."+string(filepath.Separator)) {
		return "", fmt.Errorf("unsafe path %q in the archive", name)
	}

	return filepath.Join(root, clean), nil
}

func writeFile(path string, mode os.FileMode, contents io.Reader) error {
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		return err
	}
	file, err := os.OpenFile(path, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, mode&0o755|0o600)
	if err != nil {
		return err
	}
	_, err = io.Copy(file, contents)
	if closeErr := file.Close(); err == nil {
		err = closeErr
	}

	return err
}

// untar unpacks a .tar.gz into root. Symbolic links are skipped: they are
// only npm's and npx's shell entry points, and npm is run through node.
func untar(archive, root string) error {
	file, err := os.Open(archive)
	if err != nil {
		return err
	}
	defer file.Close()
	compressed, err := gzip.NewReader(file)
	if err != nil {
		return err
	}
	reader := tar.NewReader(compressed)
	for {
		header, err := reader.Next()
		if errors.Is(err, io.EOF) {
			return nil
		}
		if err != nil {
			return err
		}
		path, err := inside(root, header.Name)
		if err != nil {
			return err
		}
		switch header.Typeflag {
		case tar.TypeDir:
			if err := os.MkdirAll(path, 0o755); err != nil {
				return err
			}
		case tar.TypeReg:
			if err := writeFile(path, header.FileInfo().Mode(), reader); err != nil {
				return err
			}
		}
	}
}

// unzip unpacks a .zip into root, with the same rules as untar.
func unzip(archive, root string) error {
	reader, err := zip.OpenReader(archive)
	if err != nil {
		return err
	}
	defer reader.Close()
	for _, entry := range reader.File {
		path, err := inside(root, entry.Name)
		if err != nil {
			return err
		}
		if entry.FileInfo().IsDir() {
			if err := os.MkdirAll(path, 0o755); err != nil {
				return err
			}

			continue
		}
		if !entry.FileInfo().Mode().IsRegular() {
			continue
		}
		contents, err := entry.Open()
		if err != nil {
			return err
		}
		err = writeFile(path, entry.FileInfo().Mode(), contents)
		contents.Close()
		if err != nil {
			return err
		}
	}

	return nil
}
