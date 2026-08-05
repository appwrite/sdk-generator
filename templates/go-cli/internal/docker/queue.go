package docker

import (
	"sync"
	"time"
)

// A debounced, lockable set of changed files. Three behaviours matter and none
// is obvious from the name:
//
//   - changes are debounced 300ms, so saving five files fires one reload
//   - while LOCKED, pushes accumulate silently; unlocking fires immediately if
//     anything arrived. That is what lets a reload triggered mid-build be
//     noticed the moment the build finishes rather than lost
//   - the file set is cleared by Lock, not by the reload -- so a change that
//     lands during a reload survives into the next one

// debounceInterval matches the TypeScript's 300ms.
const debounceInterval = 300 * time.Millisecond

// Queue collects changed files and emits debounced reload events.
type Queue struct {
	mutex    sync.Mutex
	files    []string
	locked   bool
	debounce *time.Timer
	events   chan []string
}

// NewQueue returns a queue with a buffered event channel.
//
// Buffered so a fire during a reload the consumer has not returned from does
// not block the timer goroutine.
func NewQueue() *Queue {
	return &Queue{events: make(chan []string, 1)}
}

// Events yields the changed-file sets.
func (q *Queue) Events() <-chan []string {
	return q.events
}

// Push records a changed file, scheduling a reload unless locked.
func (q *Queue) Push(file string) {
	q.mutex.Lock()
	defer q.mutex.Unlock()

	for _, existing := range q.files {
		if existing == file {
			return
		}
	}
	q.files = append(q.files, file)

	if !q.locked {
		q.trigger()
	}
}

// Lock clears the pending set and suppresses reloads.
func (q *Queue) Lock() {
	q.mutex.Lock()
	defer q.mutex.Unlock()

	q.files = nil
	q.locked = true
}

// Unlock resumes reloads, firing at once if anything arrived while locked.
func (q *Queue) Unlock() {
	q.mutex.Lock()
	defer q.mutex.Unlock()

	q.locked = false
	if len(q.files) > 0 {
		q.trigger()
	}
}

// Empty reports whether nothing is pending.
//
// Polled by a running build to decide whether it has already been made stale.
func (q *Queue) Empty() bool {
	q.mutex.Lock()
	defer q.mutex.Unlock()

	return len(q.files) == 0
}

// trigger schedules the debounced emit. The caller must hold the mutex.
func (q *Queue) trigger() {
	if q.debounce != nil {
		return
	}

	q.debounce = time.AfterFunc(debounceInterval, func() {
		q.mutex.Lock()
		files := append([]string(nil), q.files...)
		q.debounce = nil
		q.mutex.Unlock()

		// Dropped rather than blocked if a reload is already queued: the
		// consumer re-reads the full file list when it runs, so a coalesced
		// event loses nothing.
		select {
		case q.events <- files:
		default:
		}
	})
}
