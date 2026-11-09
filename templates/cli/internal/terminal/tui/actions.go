package tui

import (
	"slices"
	"strings"

	"charm.land/bubbles/v2/key"
	"charm.land/bubbles/v2/textinput"
	tea "charm.land/bubbletea/v2"
	"charm.land/lipgloss/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// deleteState is the dialog that deletes a resource once its id is typed.
type deleteState struct {
	active  bool
	ref     appwrite.Ref
	doc     appwrite.Doc
	confirm typedConfirm
	// running says the delete was asked for and has not answered yet.
	running bool
}

func (m model) askDelete(ref appwrite.Ref, d appwrite.Doc) (model, tea.Cmd) {
	c, cmd := newTypedConfirm(d.ID(), "delete the "+spec(ref.Kind).one)
	m.confirm = deleteState{active: true, ref: ref, doc: d, confirm: c}
	return m, cmd
}

func (m model) deleteUpdate(msg tea.Msg) (model, tea.Cmd) {
	c := &m.confirm
	if c.running {
		return m, nil
	}
	if k, ok := msg.(tea.KeyPressMsg); ok {
		switch {
		case key.Matches(k, m.keys.Back):
			m.confirm = deleteState{}
			return m, nil
		case key.Matches(k, m.keys.View):
			if !c.confirm.ok() {
				c.confirm.tried = true
				return m, nil
			}
			c.running = true
			m.actions++
			return m, m.deleteCmd(c.ref, c.doc.ID(), spec(c.ref.Kind).title(c.doc))
		}
	}
	var cmd tea.Cmd
	c.confirm, cmd = c.confirm.update(msg)
	return m, cmd
}

// applyDeleted closes the dialog, leaves the page of what was deleted, and reads every list of
// its kind again.
func (m model) applyDeleted(msg deletedMsg) (model, tea.Cmd) {
	m.actions--
	m.confirm = deleteState{}
	what := spec(msg.ref.Kind).one + " " + msg.title
	if msg.err != nil {
		return m.setStatus("could not delete the "+what+": "+msg.err.Error(), styles.ToneDanger)
	}
	if t := m.top(); t.page == itemPage && t.item.doc.ID() == msg.id && t.item.ref.Kind == msg.ref.Kind {
		m = m.pop()
	}
	m, status := m.setStatus("deleted the "+what, styles.ToneSuccess)
	return m, tea.Batch(status, m.reloadLists(msg.ref))
}

// reloadLists reads every list of ref in the stack again.
func (m *model) reloadLists(ref appwrite.Ref) tea.Cmd {
	same := func(l listState) bool { return l.ref.Kind == ref.Kind && slices.Equal(l.ref.Parents, ref.Parents) }
	var cmds []tea.Cmd
	for i := range m.stack {
		s := &m.stack[i]
		switch s.page {
		case listPage:
			if same(s.list) {
				cmds = append(cmds, m.load(&s.list))
			}
		case itemPage:
			for j := range s.item.lists {
				if s.item.tabs[j].list && same(s.item.lists[j]) {
					cmds = append(cmds, m.load(&s.item.lists[j]))
				}
			}
		}
	}
	return tea.Batch(cmds...)
}

func (m model) deleteView() string {
	c := m.confirm
	s := spec(c.ref.Kind)
	body := styles.Render(styles.S("this deletes the "+s.one+" ", styles.ToneBody), styles.B(s.title(c.doc), styles.ToneText), styles.S(", which cannot be undone", styles.ToneBody))
	if c.running {
		return dialog("delete "+s.one, styles.ToneDanger, body, "", styles.Accent(m.spinner.View())+" "+styles.Muted("deleting…"))
	}
	return dialog("delete "+s.one, styles.ToneDanger, body, "", c.confirm.view())
}

// executeState is the form that executes a function: the method, path, headers and body of the
// request it is given.
type executeState struct {
	active   bool
	function appwrite.Doc
	inputs   []textinput.Model
	focus    int
	running  bool
}

// The fields of the execute form, in its order.
const (
	execMethod = iota
	execPath
	execHeaders
	execBody
	execAsync
	execFields
)

var execLabels = [execFields]string{"method", "path", "headers", "body", "async"}

func (m model) askExecute(fn appwrite.Doc) (model, tea.Cmd) {
	inputs := make([]textinput.Model, execFields)
	placeholders := [execFields]string{"POST", "/", `{"content-type": "application/json"}`, `{"hello": "world"}`, "no: wait for it to return"}
	for i := range inputs {
		in := textinput.New()
		in.Prompt = ""
		in.Placeholder = placeholders[i]
		in.CharLimit = 1 << 20
		in.SetWidth(max(20, dialogTextWidth()-labelWidth-4))
		in.SetStyles(styles.TextInput())
		inputs[i] = in
	}
	inputs[execMethod].SetValue("POST")
	inputs[execPath].SetValue("/")
	inputs[execAsync].SetValue("no")
	m.exec = executeState{active: true, function: fn, inputs: inputs}
	return m, m.exec.inputs[0].Focus()
}

func (m model) executeUpdate(msg tea.Msg) (model, tea.Cmd) {
	e := &m.exec
	if e.running {
		return m, nil
	}
	if k, ok := msg.(tea.KeyPressMsg); ok {
		switch {
		case key.Matches(k, m.keys.Back):
			m.exec = executeState{}
			return m, nil
		case key.Matches(k, m.keys.Submit), key.Matches(k, m.keys.View) && e.focus == execFields-1:
			req, err := e.request()
			if err != "" {
				return m.setStatus(err, styles.ToneDanger)
			}
			e.running = true
			m.actions++
			return m, m.executeCmd(e.function.ID(), req)
		case key.Matches(k, m.keys.NextTab, m.keys.View) || k.String() == "down":
			return m, e.move(1)
		case key.Matches(k, m.keys.PrevTab) || k.String() == "up":
			return m, e.move(-1)
		}
	}
	var cmd tea.Cmd
	e.inputs[e.focus], cmd = e.inputs[e.focus].Update(msg)
	return m, cmd
}

// move puts the focus on another field, round from the last to the first.
func (e *executeState) move(by int) tea.Cmd {
	e.inputs[e.focus].Blur()
	e.focus = ((e.focus+by)%execFields + execFields) % execFields
	return e.inputs[e.focus].Focus()
}

// request is the execution the form asks for, or what is wrong with it: the headers are a json
// object of strings.
func (e executeState) request() (appwrite.Execution, string) {
	req := appwrite.Execution{
		Method: strings.ToUpper(strings.TrimSpace(e.inputs[execMethod].Value())),
		Path:   strings.TrimSpace(e.inputs[execPath].Value()),
		Body:   e.inputs[execBody].Value(),
	}
	switch strings.ToLower(strings.TrimSpace(e.inputs[execAsync].Value())) {
	case "yes", "y", "true":
		req.Async = true
	case "no", "n", "false", "":
	default:
		return req, "async is yes or no: yes queues the execution and opens it at once, no waits for it to return"
	}
	if h := strings.TrimSpace(e.inputs[execHeaders].Value()); h != "" {
		d, err := appwrite.NewDoc([]byte(h))
		if err != nil {
			return req, "the headers are not a json object of names and values, as {\"x-id\": \"1\"}"
		}
		req.Headers = map[string]string{}
		for _, f := range d.Fields() {
			req.Headers[f] = d.Str(f)
		}
	}
	return req, ""
}

func (m model) applyExecuted(msg executedMsg) (model, tea.Cmd) {
	m.actions--
	name := spec(appwrite.Functions).title(m.exec.function)
	m.exec = executeState{}
	if msg.err != nil {
		return m.setStatus("could not execute "+name+": "+msg.err.Error(), styles.ToneDanger)
	}
	reload := m.reloadLists(msg.ref)
	m, open := m.openItem(msg.ref, msg.doc)
	tone, word := styles.ToneSuccess, "executed "
	if msg.doc.Str("status") == "failed" {
		tone, word = styles.ToneWarning, "executed, and failed, "
	}
	if !msg.async {
		word += name + " in " + formatSeconds(msg.doc.Float("duration"))
	} else {
		word, tone = "queued "+name+", its page follows it", styles.ToneText
	}
	m, status := m.setStatus(word, tone)
	return m, tea.Batch(reload, open, status)
}

func (m model) executeView() string {
	e := m.exec
	name := spec(appwrite.Functions).title(e.function)
	rows := make([]string, 0, execFields)
	for i, in := range e.inputs {
		tone := styles.ToneFaint
		if i == e.focus {
			tone = styles.ToneAccent
		}
		label := styles.Fg(tone).Render(lipgloss.NewStyle().Width(labelWidth).Align(lipgloss.Right).Render(execLabels[i]))
		rows = append(rows, label+"  "+in.View())
	}
	foot := styles.Render(styles.S(m.keys.Submit.Help().Key, styles.ToneText), styles.S(" runs it · ", styles.ToneFaint),
		styles.S(m.keys.Back.Help().Key, styles.ToneText), styles.S(" cancels", styles.ToneFaint))
	if e.running {
		foot = styles.Accent(m.spinner.View()) + " " + styles.Muted("executing, waiting for it to return…")
	}
	form := lipgloss.NewStyle().Align(lipgloss.Left).Render(strings.Join(rows, "\n"))
	return dialog("execute "+name, styles.ToneAccent, form, "", foot)
}

// download saves a file of a bucket in the working directory.
func (m model) download(ref appwrite.Ref, file appwrite.Doc) (model, tea.Cmd) {
	if len(ref.Parents) == 0 {
		return m, nil
	}
	m.actions++
	m, status := m.setStatus("downloading "+saveName(file.Str("name"), file.ID())+"…", styles.ToneText)
	return m, tea.Batch(status, m.downloadCmd(ref.Parents[0], file))
}

func (m model) applyDownloaded(msg downloadedMsg) (model, tea.Cmd) {
	m.actions--
	if msg.err != nil {
		return m.setStatus("could not download: "+msg.err.Error(), styles.ToneDanger)
	}
	return m.setStatus("saved "+msg.path+", "+formatBytes(msg.size), styles.ToneSuccess)
}
