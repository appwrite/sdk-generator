package config

import (
	"os"
	"path/filepath"
)

// writeFileAtomically replaces a file's contents in a single step.
//
// os.WriteFile truncates in place, so a crash mid-write leaves a partial file --
// and LoadGlobal treats an unparseable prefs.json as empty preferences, so that
// silently discards every session rather than surfacing an error.
//
// Local.Write rewrites each include file and then the root, which renaming
// cannot make atomic as a whole; it does guarantee each file is entirely its old
// or its new contents, turning "some files are corrupt" into "some files are
// older than others".
//
// The temporary file sits alongside the destination because rename is only
// atomic within one filesystem.
func writeFileAtomically(path string, data []byte, permissions os.FileMode) error {
	temporary, err := os.CreateTemp(filepath.Dir(path), "."+filepath.Base(path)+".*")
	if err != nil {
		return err
	}
	temporaryPath := temporary.Name()

	// Any failure past this point takes the temporary file with it, rather than
	// leaving it next to the real one for someone to find later.
	abandon := func(err error) error {
		temporary.Close()
		_ = os.Remove(temporaryPath)

		return err
	}

	if _, err := temporary.Write(data); err != nil {
		return abandon(err)
	}

	// CreateTemp already makes the file 0600, but the caller states the mode it
	// wants rather than inheriting one that happens to be right.
	if err := temporary.Chmod(permissions); err != nil {
		return abandon(err)
	}

	// Flushed before the rename. Without this the rename can reach the disk while
	// the contents have not, which converts a crash into an empty file -- exactly
	// the outcome the rename was supposed to rule out.
	if err := temporary.Sync(); err != nil {
		return abandon(err)
	}
	if err := temporary.Close(); err != nil {
		_ = os.Remove(temporaryPath)

		return err
	}

	if err := os.Rename(temporaryPath, path); err != nil {
		_ = os.Remove(temporaryPath)

		return err
	}

	return nil
}
