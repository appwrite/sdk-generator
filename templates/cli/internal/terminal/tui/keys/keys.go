// Package keys holds the keybindings of the terminal interface.
package keys

import "charm.land/bubbles/v2/key"

// KeyMap holds the keybindings shared by the views.
type KeyMap struct {
	Up       key.Binding
	Down     key.Binding
	PageUp   key.Binding
	PageDown key.Binding
	Top      key.Binding
	Bottom   key.Binding
	Left     key.Binding
	Right    key.Binding
	Help     key.Binding
	View     key.Binding
	Filter   key.Binding
	NextTab  key.Binding
	PrevTab  key.Binding
	// Tab opens a page's tab by its number, as the tab row numbers them.
	Tab key.Binding
	// More reads the next page of a list the server holds more of than it sent.
	More     key.Binding
	Execute  key.Binding
	Download key.Binding
	Delete   key.Binding
	Submit   key.Binding
	Copy     key.Binding
	// CopyJSON copies the whole of a resource, as its json tab shows it.
	CopyJSON key.Binding
	Edit     key.Binding
	Create   key.Binding
	Refresh  key.Binding
	Pause    key.Binding
	Yes      key.Binding
	No       key.Binding
	Back     key.Binding
	Quit     key.Binding
	// ForceQuit quits from any page, also while a text input has focus.
	ForceQuit key.Binding
}

// Keys are the keybindings the README lists. A help key names every key of its binding, as the
// footer and the key reference write them: arrows as ↑ ↓ ← →, alternatives joined with /.
var Keys = KeyMap{
	Up:        key.NewBinding(key.WithKeys("up", "k"), key.WithHelp("↑/k", "up")),
	Down:      key.NewBinding(key.WithKeys("down", "j"), key.WithHelp("↓/j", "down")),
	PageUp:    key.NewBinding(key.WithKeys("pgup"), key.WithHelp("pgup", "page up")),
	PageDown:  key.NewBinding(key.WithKeys("pgdown"), key.WithHelp("pgdn", "page down")),
	Top:       key.NewBinding(key.WithKeys("g", "home"), key.WithHelp("g/home", "top")),
	Bottom:    key.NewBinding(key.WithKeys("G", "end"), key.WithHelp("G/end", "bottom")),
	Left:      key.NewBinding(key.WithKeys("left", "h"), key.WithHelp("←/h", "left")),
	Right:     key.NewBinding(key.WithKeys("right", "l"), key.WithHelp("→/l", "right")),
	Help:      key.NewBinding(key.WithKeys("?"), key.WithHelp("?", "help")),
	View:      key.NewBinding(key.WithKeys("enter"), key.WithHelp("enter", "open")),
	Filter:    key.NewBinding(key.WithKeys("/"), key.WithHelp("/", "filter")),
	NextTab:   key.NewBinding(key.WithKeys("tab"), key.WithHelp("tab", "next tab")),
	PrevTab:   key.NewBinding(key.WithKeys("shift+tab"), key.WithHelp("shift+tab", "previous tab")),
	Tab:       key.NewBinding(key.WithKeys("1", "2", "3", "4", "5", "6", "7", "8", "9"), key.WithHelp("1-9", "open tab")),
	More:      key.NewBinding(key.WithKeys("m"), key.WithHelp("m", "more")),
	Execute:   key.NewBinding(key.WithKeys("x"), key.WithHelp("x", "execute")),
	Download:  key.NewBinding(key.WithKeys("ctrl+s"), key.WithHelp("ctrl+s", "download")),
	Delete:    key.NewBinding(key.WithKeys("ctrl+d"), key.WithHelp("ctrl+d", "delete")),
	Submit:    key.NewBinding(key.WithKeys("ctrl+r"), key.WithHelp("ctrl+r", "run")),
	Copy:      key.NewBinding(key.WithKeys("c"), key.WithHelp("c", "copy id")),
	CopyJSON:  key.NewBinding(key.WithKeys("C", "shift+c"), key.WithHelp("shift+c", "copy json")),
	Edit:      key.NewBinding(key.WithKeys("e"), key.WithHelp("e", "edit")),
	Create:    key.NewBinding(key.WithKeys("ctrl+n"), key.WithHelp("ctrl+n", "new row")),
	Refresh:   key.NewBinding(key.WithKeys("r"), key.WithHelp("r", "refresh")),
	Pause:     key.NewBinding(key.WithKeys("p"), key.WithHelp("p", "pause")),
	Yes:       key.NewBinding(key.WithKeys("y"), key.WithHelp("y", "yes")),
	No:        key.NewBinding(key.WithKeys("n"), key.WithHelp("n", "no")),
	Back:      key.NewBinding(key.WithKeys("esc"), key.WithHelp("esc", "back")),
	Quit:      key.NewBinding(key.WithKeys("q"), key.WithHelp("q", "back")),
	ForceQuit: key.NewBinding(key.WithKeys("ctrl+c"), key.WithHelp("ctrl+c", "quit")),
}
