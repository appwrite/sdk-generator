package tui

import (
	"errors"
	"fmt"
	"os"
	"path"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// listLoadedMsg is a page of a list. With more it follows the resources the list holds already.
type listLoadedMsg struct {
	gen  int
	page appwrite.Page
	more bool
	err  error
}

// countLoadedMsg is how many resources of a kind the project holds, for its overview.
type countLoadedMsg struct {
	gen   int
	kind  appwrite.Kind
	count int
	err   error
}

// docLoadedMsg is a resource read again for its page.
type docLoadedMsg struct {
	gen int
	doc appwrite.Doc
	err error
}

// pingMsg is a round trip to the instance, for the header.
type pingMsg struct {
	rtt time.Duration
	err error
}

type deletedMsg struct {
	ref   appwrite.Ref
	id    string
	title string
	err   error
}

type executedMsg struct {
	ref   appwrite.Ref
	doc   appwrite.Doc
	async bool
	err   error
}

type downloadedMsg struct {
	path string
	size int64
	err  error
}

type refreshTickMsg struct {
	gen int
}

type statusClearMsg struct {
	gen int
}

// listCmd reads a page of a list: from its start, or after the resources it holds with more.
func (m model) listCmd(gen int, l listState, more bool) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	opts := appwrite.ListOptions{Search: l.search, Queries: l.queries}
	if more && len(l.docs) > 0 {
		opts.After = l.docs[len(l.docs)-1].ID()
		opts.Offset = len(l.docs)
	}
	ref, b := l.ref, l.b
	return func() tea.Msg {
		defer cancel()
		p, err := b.List(ctx, ref, opts)
		return listLoadedMsg{gen: gen, page: p, more: more, err: err}
	}
}

func (m model) countCmd(b backend, gen int, kind appwrite.Kind) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	return func() tea.Msg {
		defer cancel()
		n, err := b.Count(ctx, appwrite.Ref{Kind: kind})
		return countLoadedMsg{gen: gen, kind: kind, count: n, err: err}
	}
}

func (m model) getCmd(b backend, gen int, ref appwrite.Ref, id string) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	return func() tea.Msg {
		defer cancel()
		d, err := b.Get(ctx, ref, id)
		return docLoadedMsg{gen: gen, doc: d, err: err}
	}
}

func (m model) pingCmd() tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	b := m.top().b
	return func() tea.Msg {
		defer cancel()
		rtt, err := b.Ping(ctx)
		return pingMsg{rtt: rtt, err: err}
	}
}

func (m model) deleteCmd(ref appwrite.Ref, id, title string) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	b := m.top().b
	return func() tea.Msg {
		defer cancel()
		return deletedMsg{ref: ref, id: id, title: title, err: b.Delete(ctx, ref, id)}
	}
}

// executeCmd runs a function and waits for it to return, as the console's execute does.
func (m model) executeCmd(function string, e appwrite.Execution) tea.Cmd {
	ctx, cancel := m.withTimeout(executeTimeout)
	ref := appwrite.Ref{Kind: appwrite.Executions, Parents: []string{function}}
	b := m.top().b
	return func() tea.Msg {
		defer cancel()
		d, err := b.Execute(ctx, function, e)
		return executedMsg{ref: ref, doc: d, async: e.Async, err: err}
	}
}

// downloadCmd saves a file of a bucket in the working directory, under its name, numbered when a
// file of that name is there already, so nothing is overwritten.
func (m model) downloadCmd(bucket string, file appwrite.Doc) tea.Cmd {
	ctx, cancel := m.withTimeout(downloadTimeout)
	b := m.top().b
	return func() tea.Msg {
		defer cancel()
		f, saved, err := createFree(saveName(file.Str("name"), file.ID()))
		if err != nil {
			return downloadedMsg{err: err}
		}
		n, err := b.Download(ctx, bucket, file.ID(), f)
		if cerr := f.Close(); err == nil {
			err = cerr
		}
		if err != nil {
			_ = os.Remove(saved)
			return downloadedMsg{path: saved, err: err}
		}
		return downloadedMsg{path: saved, size: n}
	}
}

// saveName is the name a download is saved under: the file's own, cut to its last element so it
// stays in the working directory, or its id when that leaves nothing. The name is split on either
// slash with path, not filepath, so it is cut the same on every system.
func saveName(name, id string) string {
	name = path.Base(path.Clean("/" + strings.ReplaceAll(name, "\\", "/")))
	if name == "/" || name == "." || name == "" {
		return id
	}
	return name
}

// createFree creates name in the working directory, or name-1, name-2 and so on before its
// extension when it is taken. name is a file's name cut to its last element by saveName, so it
// stays in the working directory.
//
//nolint:gosec
func createFree(name string) (*os.File, string, error) {
	ext := filepath.Ext(name)
	stem := strings.TrimSuffix(name, ext)
	for i := range 1000 {
		saved := name
		if i > 0 {
			saved = stem + "-" + strconv.Itoa(i) + ext
		}
		f, err := os.OpenFile(saved, os.O_WRONLY|os.O_CREATE|os.O_EXCL, 0o644)
		switch {
		case err == nil:
			return f, saved, nil
		case !errors.Is(err, os.ErrExist):
			return nil, saved, fmt.Errorf("saving %s: %w", saved, err)
		}
	}
	return nil, name, fmt.Errorf("saving %s: a thousand files of that name are here already", name)
}

func (m model) scheduleRefresh() (model, tea.Cmd) {
	m.refreshGen++
	return m, refreshTick(m.refreshGen)
}

func refreshTick(gen int) tea.Cmd {
	return tea.Tick(refreshEvery, func(time.Time) tea.Msg { return refreshTickMsg{gen: gen} })
}

// setStatus shows text in the footer for a few seconds, in a tone.
func (m model) setStatus(text string, tone styles.Tone) (model, tea.Cmd) {
	m.statusGen++
	gen := m.statusGen
	m.status, m.statusTone = text, tone
	return m, tea.Tick(4*time.Second, func(time.Time) tea.Msg { return statusClearMsg{gen: gen} })
}
