package tui

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"os"
	"os/exec"
	"runtime"
	"strings"

	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// editorDoneMsg is the editor closed on the values of a row: of the row of id, or of a new one
// when id is empty, as written to path, which held before when it opened.
type editorDoneMsg struct {
	ref    appwrite.Ref
	id     string
	path   string
	before []byte
	err    error
}

// savedMsg is a row saved: changed, or created.
type savedMsg struct {
	ref     appwrite.Ref
	doc     appwrite.Doc
	created bool
	err     error
}

// editor is the command a person edits in: $VISUAL, then $EDITOR, as a command line with its
// arguments, as code --wait, and vi without either, or notepad on windows.
func editor(path string) *exec.Cmd {
	fallback := "vi"
	if runtime.GOOS == "windows" {
		fallback = "notepad"
	}
	line := strings.Fields(cmpOr(os.Getenv("VISUAL"), os.Getenv("EDITOR"), fallback))
	//nolint:gosec
	return exec.Command(line[0], append(line[1:], path)...)
}

// rowData is what a row holds of its own, its columns but not the api's $ fields, as the json
// an edit starts from.
func rowData(d appwrite.Doc) map[string]any {
	out := map[string]any{}
	for _, f := range d.Fields() {
		if !strings.HasPrefix(f, "$") {
			out[f] = d.Value(f)
		}
	}
	return out
}

// newRowData is the template of a new row of a table: each of its columns, at its default or
// null.
func newRowData(table appwrite.Doc) map[string]any {
	out := map[string]any{}
	list, _ := table.Value("columns").([]any)
	for _, c := range list {
		if col, ok := c.(map[string]any); ok {
			if key := appwrite.Format(col["key"]); key != "" {
				out[key] = col["default"]
			}
		}
	}
	return out
}

// openEditor writes data as indented json to a file of its own and opens the editor on it,
// handing the interface back once it closes.
func (m model) openEditor(ref appwrite.Ref, id string, data map[string]any) (model, tea.Cmd) {
	body, err := json.MarshalIndent(data, "", "  ")
	if err != nil {
		return m.setStatus("could not write the row as json: "+err.Error(), styles.ToneDanger)
	}
	body = append(body, '\n')
	f, err := os.CreateTemp("", "appwrite-tui-row-*.json")
	if err != nil {
		return m.setStatus("could not make a file to edit the row in: "+err.Error(), styles.ToneDanger)
	}
	path := f.Name()
	_, err = f.Write(body)
	if cerr := f.Close(); err == nil {
		err = cerr
	}
	if err != nil {
		_ = os.Remove(path)
		return m.setStatus("could not make a file to edit the row in: "+err.Error(), styles.ToneDanger)
	}
	return m, tea.ExecProcess(editor(path), func(err error) tea.Msg {
		return editorDoneMsg{ref: ref, id: id, path: path, before: body, err: err}
	})
}

// editRow opens a row's values in the editor.
func (m model) editRow(ref appwrite.Ref, d appwrite.Doc) (model, tea.Cmd) {
	return m.openEditor(ref, d.ID(), rowData(d))
}

// newRow opens the template of a new row of a table in the editor.
func (m model) newRow(ref appwrite.Ref, table appwrite.Doc) (model, tea.Cmd) {
	return m.openEditor(ref, "", newRowData(table))
}

// applyEditorDone saves what the editor left, unless it is what it opened on. What is not a json
// object is kept in its file, which a dialog names in full, so the edit is not lost.
func (m model) applyEditorDone(msg editorDoneMsg) (model, tea.Cmd) {
	if msg.err != nil {
		_ = os.Remove(msg.path)
		return m.setStatus("the editor did not close cleanly, nothing was saved: "+msg.err.Error()+": set $EDITOR to the editor to use", styles.ToneDanger)
	}
	body, err := os.ReadFile(msg.path)
	if err != nil {
		return m.setStatus("could not read the edit back: "+err.Error(), styles.ToneDanger)
	}
	if bytes.Equal(bytes.TrimSpace(body), bytes.TrimSpace(msg.before)) {
		_ = os.Remove(msg.path)
		return m.setStatus("nothing changed, nothing saved", styles.ToneWarning)
	}
	data, err := decodeObject(body)
	if err != nil {
		m.error = fmt.Sprintf("the edit is not a json object, so nothing was saved: %s\n\nit is kept in %s", err, msg.path)
		return m, nil
	}
	_ = os.Remove(msg.path)
	m.actions++
	return m, m.saveCmd(msg.ref, msg.id, data)
}

// decodeObject reads a json object, its numbers as they were written.
func decodeObject(body []byte) (map[string]any, error) {
	d := json.NewDecoder(bytes.NewReader(body))
	d.UseNumber()
	var data map[string]any
	if err := d.Decode(&data); err != nil {
		return nil, err
	}
	if data == nil {
		return nil, errors.New("it is null")
	}
	return data, nil
}

// saveCmd changes the row of id, or creates one when id is empty.
func (m model) saveCmd(ref appwrite.Ref, id string, data map[string]any) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	b := m.top().b
	return func() tea.Msg {
		defer cancel()
		if id == "" {
			d, err := b.Create(ctx, ref, data)
			return savedMsg{ref: ref, doc: d, created: true, err: err}
		}
		d, err := b.Update(ctx, ref, id, data)
		return savedMsg{ref: ref, doc: d, err: err}
	}
}

// applySaved says what was saved, shows the row as it is now on its page, and reads its lists
// again.
func (m model) applySaved(msg savedMsg) (model, tea.Cmd) {
	m.actions--
	if msg.err != nil {
		what := "save the row"
		if msg.created {
			what = "create the row"
		}
		return m.setStatus("could not "+what+": "+msg.err.Error(), styles.ToneDanger)
	}
	for i := range m.stack {
		if it := &m.stack[i].item; m.stack[i].page == itemPage && it.ref.Kind == msg.ref.Kind && it.doc.ID() == msg.doc.ID() {
			it.doc = msg.doc
			it.redraw()
		}
	}
	word := "saved the row "
	if msg.created {
		word = "created the row "
	}
	m, status := m.setStatus(word+msg.doc.ID(), styles.ToneSuccess)
	return m, tea.Batch(status, m.reloadLists(msg.ref))
}
