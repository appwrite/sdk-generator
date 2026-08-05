package watch

import (
	"io/fs"
	"os"
	"path/filepath"
	"strings"

	"github.com/fsnotify/fsnotify"
)

// Replaces chokidar for `run`'s live reload.
//
// fsnotify watches directories, not trees, so every directory has to be
// registered and a newly created one registered as it appears -- otherwise
// creating a folder and editing inside it is silently missed. chokidar hides
// that; this does it explicitly.

// Ignored reports whether a path relative to the root should be skipped.
type Ignored func(relative string) bool

// Watcher reports changes beneath a directory.
type Watcher struct {
	watcher *fsnotify.Watcher
	root    string
	ignored Ignored
	done    chan struct{}
}

// Start watches a directory tree, calling changed with each relative,
// slash-separated path.
//
// An ignored directory is not descended into, so a node_modules with tens of
// thousands of files costs nothing -- which is also the difference between
// working and hitting the per-process watch limit on Linux.
func Start(root string, ignored Ignored, changed func(string)) (*Watcher, error) {
	watcher, err := fsnotify.NewWatcher()
	if err != nil {
		return nil, err
	}

	w := &Watcher{watcher: watcher, root: root, ignored: ignored, done: make(chan struct{})}

	if err := w.addTree(root); err != nil {
		watcher.Close()

		return nil, err
	}

	go w.run(changed)

	return w, nil
}

// addTree registers a directory and every non-ignored directory beneath it.
func (w *Watcher) addTree(directory string) error {
	return filepath.WalkDir(directory, func(path string, entry fs.DirEntry, err error) error {
		if err != nil {
			// A directory that vanished mid-walk is not an error: this runs
			// while the user is editing.
			return nil
		}
		if !entry.IsDir() {
			return nil
		}

		relative, err := w.relative(path)
		if err != nil {
			return nil
		}
		if relative != "" && w.ignored(relative) {
			return filepath.SkipDir
		}

		return w.watcher.Add(path)
	})
}

// relative renders a path relative to the root, slash-separated.
func (w *Watcher) relative(path string) (string, error) {
	relative, err := filepath.Rel(w.root, path)
	if err != nil {
		return "", err
	}
	if relative == "." {
		return "", nil
	}

	return filepath.ToSlash(relative), nil
}

func (w *Watcher) run(changed func(string)) {
	for {
		select {
		case <-w.done:
			return

		case event, ok := <-w.watcher.Events:
			if !ok {
				return
			}
			w.handle(event, changed)

		case _, ok := <-w.watcher.Errors:
			if !ok {
				return
			}
			// Watch errors are dropped. They are almost always a file removed
			// between the event and the stat; stopping the whole reload loop
			// over one would be worse than missing it.
		}
	}
}

func (w *Watcher) handle(event fsnotify.Event, changed func(string)) {
	relative, err := w.relative(event.Name)
	if err != nil || relative == "" {
		return
	}

	// Checked before the stat: an ignored path should not even be probed, and
	// the ignore rules are what keep .appwrite's own writes from looping.
	if w.ignored(relative) {
		return
	}

	if event.Has(fsnotify.Create) {
		if info, err := os.Stat(event.Name); err == nil && info.IsDir() {
			// A directory created after the initial walk has to be registered
			// or nothing inside it is ever seen.
			_ = w.addTree(event.Name)
		}
	}

	changed(relative)
}

// Close stops watching.
func (w *Watcher) Close() error {
	close(w.done)

	return w.watcher.Close()
}

// PrefixIgnored adapts a path-based predicate so a directory is skipped when
// the directory itself, with a trailing slash, is ignored.
func PrefixIgnored(ignores func(string) bool) Ignored {
	return func(relative string) bool {
		return ignores(relative) || ignores(strings.TrimSuffix(relative, "/")+"/")
	}
}
