package watch

import (
	"crypto/sha256"
	"io/fs"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"time"

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
	watcher      *fsnotify.Watcher
	root         string
	ignored      Ignored
	fingerprints map[string][sha256.Size]byte
	done         chan struct{}
	finished     chan struct{}
	stop         sync.Once
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

	w := &Watcher{
		watcher:      watcher,
		root:         root,
		ignored:      ignored,
		fingerprints: make(map[string][sha256.Size]byte),
		done:         make(chan struct{}),
		finished:     make(chan struct{}),
	}

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
		relative, err := w.relative(path)
		if err != nil {
			return nil
		}
		if relative != "" && w.ignored(relative) {
			if entry.IsDir() {
				return filepath.SkipDir
			}

			return nil
		}

		if !entry.IsDir() {
			if fingerprint, err := fingerprint(path); err == nil {
				w.fingerprints[relative] = fingerprint
			}

			return nil
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

// Wait for a quiet interval before fingerprinting: a truncate-and-rewrite can
// otherwise be observed as an empty file before the writer restores its bytes.
const settleDelay = 100 * time.Millisecond

// A tree that never falls quiet would defer its changes forever, so a path is
// fingerprinted this long after its first event however busy the tree stays.
const settleLimit = time.Second

// pending is a change waiting for its path to settle.
//
// The wait is per path rather than shared: a file first touched late in a busy
// stretch still needs its own quiet interval, and a shared deadline would hand
// it whatever milliseconds happened to be left.
type pending struct {
	event    fsnotify.Event
	quiet    time.Time // no further events since
	deadline time.Time // measured from the first event
}

// due reports when the change has to be handled, whether or not it settled.
func (p *pending) due() time.Time {
	if p.deadline.Before(p.quiet) {
		return p.deadline
	}

	return p.quiet
}

func (w *Watcher) run(changed func(string)) {
	defer close(w.finished)

	changes := make(map[string]*pending)
	timer := time.NewTimer(settleDelay)
	timer.Stop()
	defer timer.Stop()

	for {
		select {
		case <-w.done:
			return

		case event, ok := <-w.watcher.Events:
			if !ok {
				return
			}
			relative, err := w.relative(event.Name)
			if err != nil || relative == "" || w.ignored(relative) {
				continue
			}
			now := time.Now()
			change, waiting := changes[event.Name]
			if !waiting {
				change = &pending{deadline: now.Add(settleLimit)}
				changes[event.Name] = change
			}
			event.Op |= change.event.Op
			change.event = event
			change.quiet = now.Add(settleDelay)
			schedule(timer, changes, now)

		case <-timer.C:
			now := time.Now()
			for path, change := range changes {
				if now.Before(change.due()) {
					continue
				}
				w.handle(change.event, changed)
				delete(changes, path)
			}
			schedule(timer, changes, now)

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

// schedule arms the timer for the earliest change due.
func schedule(timer *time.Timer, changes map[string]*pending, now time.Time) {
	var next time.Time
	for _, change := range changes {
		if due := change.due(); next.IsZero() || due.Before(next) {
			next = due
		}
	}
	if next.IsZero() {
		timer.Stop()

		return
	}

	timer.Reset(max(0, next.Sub(now)))
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

	info, statErr := os.Stat(event.Name)
	if statErr == nil && info.IsDir() {
		if event.Has(fsnotify.Create) {
			// A directory created after the initial walk has to be registered
			// or nothing inside it is ever seen.
			_ = w.addTree(event.Name)
			changed(relative)
		}

		// Directory metadata is not part of the function bundle.
		return
	}
	if statErr != nil {
		// A missing file is a real change only when it, or a directory beneath
		// it, existed in the last snapshot. Unknown paths can disappear between
		// the event and the stat.
		removed := false
		prefix := strings.TrimSuffix(relative, "/") + "/"
		for known := range w.fingerprints {
			if known == relative || strings.HasPrefix(known, prefix) {
				delete(w.fingerprints, known)
				removed = true
			}
		}
		if removed {
			changed(relative)
		}

		return
	}

	fingerprint, err := fingerprint(event.Name)
	if err != nil {
		return
	}

	previous, existed := w.fingerprints[relative]
	w.fingerprints[relative] = fingerprint
	if existed && previous == fingerprint {
		return
	}

	changed(relative)
}

// fingerprint identifies file contents rather than filesystem metadata.
// Editors and monorepo tools commonly touch or chmod source files without
// changing them; treating those notifications as edits creates reload loops.
func fingerprint(path string) ([sha256.Size]byte, error) {
	contents, err := os.ReadFile(path)
	if err != nil {
		return [sha256.Size]byte{}, err
	}

	return sha256.Sum256(contents), nil
}

// Close stops watching and waits for any change already being handled, so no
// callback runs once it returns. It is safe to call more than once.
func (w *Watcher) Close() error {
	var err error
	w.stop.Do(func() {
		close(w.done)
		err = w.watcher.Close()
	})
	<-w.finished

	return err
}

// PrefixIgnored adapts a path-based predicate so a directory is skipped when
// the directory itself, with a trailing slash, is ignored.
func PrefixIgnored(ignores func(string) bool) Ignored {
	return func(relative string) bool {
		return ignores(relative) || ignores(strings.TrimSuffix(relative, "/")+"/")
	}
}
