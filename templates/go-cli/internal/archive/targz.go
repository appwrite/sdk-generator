package archive

import (
	"archive/tar"
	"compress/gzip"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"
)

// gzipped tar handling, shared by the function emulator's hot swap and by
// `pull`'s deployment download.
//
// Extraction refuses an entry that escapes the destination. Both callers feed
// it an archive the API produced, but an archive is untrusted input to the
// process unpacking it -- a `../../.ssh/authorized_keys` entry is the whole
// reason this check exists.

// ExtractTarGz unpacks an archive into a directory.
func ExtractTarGz(path, destination string) error {
	file, err := os.Open(path)
	if err != nil {
		return err
	}
	defer file.Close()

	decompressed, err := gzip.NewReader(file)
	if err != nil {
		return err
	}
	defer decompressed.Close()

	reader := tar.NewReader(decompressed)
	for {
		header, err := reader.Next()
		if err == io.EOF {
			return nil
		}
		if err != nil {
			return err
		}

		target, err := SafeJoin(destination, header.Name)
		if err != nil {
			return err
		}

		switch header.Typeflag {
		case tar.TypeDir:
			if err := os.MkdirAll(target, 0o755); err != nil {
				return err
			}
		case tar.TypeReg:
			if err := os.MkdirAll(filepath.Dir(target), 0o755); err != nil {
				return err
			}
			if err := writeEntry(target, reader, os.FileMode(header.Mode).Perm()); err != nil {
				return err
			}
		}
		// Other entry types -- symlinks, devices, hard links -- are skipped.
		// The bundle is repacked immediately afterwards, so dropping them
		// changes what the container sees; but honouring a symlink from an
		// archive is how a path traversal gets in, and the build output the
		// runtimes produce contains none.
	}
}

// SafeJoin resolves an archive entry inside the destination, refusing to escape.
//
// An archive entry named `../../.ssh/authorized_keys` would otherwise be
// written outside the staging directory. The bundle comes from the user's own
// build, but it is still untrusted input to this process.
func SafeJoin(destination, name string) (string, error) {
	target := filepath.Join(destination, filepath.FromSlash(name))

	relative, err := filepath.Rel(destination, target)
	if err != nil {
		return "", err
	}
	if relative == ".." || strings.HasPrefix(relative, ".."+string(filepath.Separator)) {
		return "", fmt.Errorf("archive entry %q escapes the extraction directory", name)
	}

	return target, nil
}

func writeEntry(target string, reader io.Reader, mode os.FileMode) error {
	if mode == 0 {
		mode = 0o644
	}

	file, err := os.OpenFile(target, os.O_CREATE|os.O_TRUNC|os.O_WRONLY, mode)
	if err != nil {
		return err
	}
	defer file.Close()

	_, err = io.Copy(file, reader)

	return err
}

// CreateTarGz packs a directory into a gzipped archive.
//
// Written to a temporary file and renamed, so an interrupted repack cannot
// leave a truncated bundle where the container expects a valid one.
func CreateTarGz(path, directory string) error {
	temporary, err := os.CreateTemp(filepath.Dir(path), ".build-*.tar.gz")
	if err != nil {
		return err
	}
	temporaryPath := temporary.Name()
	defer os.Remove(temporaryPath)

	compressed := gzip.NewWriter(temporary)
	writer := tar.NewWriter(compressed)

	err = filepath.WalkDir(directory, func(path string, entry os.DirEntry, err error) error {
		if err != nil {
			return err
		}

		relative, err := filepath.Rel(directory, path)
		if err != nil {
			return err
		}
		if relative == "." {
			return nil
		}

		info, err := entry.Info()
		if err != nil {
			return err
		}

		header, err := tar.FileInfoHeader(info, "")
		if err != nil {
			return err
		}
		header.Name = "./" + filepath.ToSlash(relative)
		if entry.IsDir() {
			header.Name += "/"
		}

		if err := writer.WriteHeader(header); err != nil {
			return err
		}
		if entry.IsDir() || !info.Mode().IsRegular() {
			return nil
		}

		file, err := os.Open(path)
		if err != nil {
			return err
		}
		defer file.Close()

		_, err = io.Copy(writer, file)

		return err
	})
	if err != nil {
		writer.Close()
		compressed.Close()
		temporary.Close()

		return err
	}

	if err := writer.Close(); err != nil {
		return err
	}
	if err := compressed.Close(); err != nil {
		return err
	}
	if err := temporary.Close(); err != nil {
		return err
	}

	return os.Rename(temporaryPath, path)
}
