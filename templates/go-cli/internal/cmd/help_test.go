package cmd

import (
	"bytes"
	"strings"
	"testing"
)

// The main help screen is the first thing a new user sees, and it is the one
// screen cobra cannot produce: its default lists 40-odd services
// alphabetically, each with a paragraph of API prose.
//
// These assert the layout rather than a byte golden, because the command set
// comes from the spec -- a service added upstream would fail a golden without
// anything being wrong.

// theRealDescription is the paragraph the shipped CLI carries, kept here so the
// wrap is checked against real input rather than against the placeholder the
// example generation uses.
const theRealDescription = "Appwrite is an open-source self-hosted backend " +
	"server that abstracts and simplifies complex and repetitive development " +
	"tasks behind a very simple REST API"

func TestDescriptionWrapsTheSameWayCommanderDoes(t *testing.T) {
	// Copied from `appwrite -h` on the TypeScript CLI.
	want := strings.Join([]string{
		"  Appwrite is an open-source self-hosted backend server that abstracts and",
		"  simplifies complex and repetitive development tasks behind a very simple",
		"  REST API",
	}, "\n")

	got := wrapHelpText(theRealDescription, helpMaxWidth-helpGap-helpGap, helpIndent)
	if got != want {
		t.Errorf("wrapped paragraph differs\n got:\n%s\nwant:\n%s", got, want)
	}
}

func TestHelpSectionsAppearInOrder(t *testing.T) {
	screen := RenderMainHelp(NewRootCommand())

	// USAGE first, then the groups that have rows, then OTHER, then OPTIONS.
	// A group with no rows is omitted rather than printed empty -- which is the
	// usual case for the six-service mock spec the conformance build uses -- so
	// what is asserted is the order of the headings that ARE there.
	order := []string{"USAGE"}
	for _, group := range helpGroups {
		order = append(order, group.title)
	}
	order = append(order, "OTHER", "OPTIONS")

	rank := map[string]int{}
	for index, heading := range order {
		rank[heading] = index
	}

	previous := -1
	seen := 0
	for _, line := range strings.Split(screen, "\n") {
		at, ok := rank[line]
		if !ok {
			continue
		}
		if at <= previous {
			t.Errorf("%q is out of order on the screen", line)
		}
		previous = at
		seen++
	}

	// USAGE and OPTIONS are unconditional, and at least one group has to have
	// rows or the screen lists nothing at all.
	if seen < 3 {
		t.Errorf("only %d of the expected headings are on the screen", seen)
	}
	for _, required := range []string{"USAGE", "OPTIONS"} {
		if !strings.Contains(screen, "\n"+required+"\n") {
			t.Errorf("%q is missing from the screen", required)
		}
	}
}

func TestHelpListsGroupedCommandsWithTheirSummaries(t *testing.T) {
	root := NewRootCommand()
	screen := RenderMainHelp(root)

	for _, group := range helpGroups {
		for _, path := range group.commands {
			// A group may name a command this spec does not produce.
			if resolveCommand(root, path) == nil {
				continue
			}

			summary, ok := helpSummaries[path]
			if !ok {
				t.Errorf("%q is grouped but has no summary", path)

				continue
			}

			if !strings.Contains(screen, path) || !strings.Contains(screen, summary) {
				t.Errorf("%q is missing from the screen", path)
			}
		}
	}
}

// The screen is grouped by hand, so a service added to the spec would be
// invisible without OTHER catching it.
func TestEveryVisibleRootCommandIsListedSomewhere(t *testing.T) {
	root := NewRootCommand()
	screen := RenderMainHelp(root)

	for _, child := range root.Commands() {
		if !isListedInHelp(child) {
			continue
		}

		if !strings.Contains(screen, child.Name()) {
			t.Errorf("`%s` is in the tree but not on the help screen", child.Name())
		}
	}
}

func TestSummariesLineUpInOneColumn(t *testing.T) {
	screen := RenderMainHelp(NewRootCommand())

	// Every listed command is a two-space-indented row; they share one column.
	column := -1
	for _, line := range strings.Split(screen, "\n") {
		if !strings.HasPrefix(line, "  ") || strings.HasPrefix(line, "   ") {
			continue
		}
		name, summary, found := strings.Cut(strings.TrimPrefix(line, "  "), "  ")
		if !found || strings.Contains(name, " ") || summary == "" {
			continue
		}
		if _, ok := helpSummaries[name]; !ok {
			continue
		}

		at := strings.Index(line, strings.TrimLeft(summary, " "))
		if column == -1 {
			column = at

			continue
		}
		if at != column {
			t.Errorf("summary for %q starts at column %d, the others at %d", name, at, column)
		}
	}

	if column == -1 {
		t.Fatal("no command rows were found on the screen")
	}
}

func TestOptionsFollowTheDeclaredOrder(t *testing.T) {
	screen := RenderMainHelp(NewRootCommand())

	_, options, found := strings.Cut(screen, "\nOPTIONS\n")
	if !found {
		t.Fatal("the screen has no OPTIONS section")
	}
	options, _, _ = strings.Cut(options, "\n\n")

	listed := make([]string, 0)
	for _, line := range strings.Split(options, "\n") {
		fields := strings.Fields(line)
		for _, field := range fields {
			if strings.HasPrefix(field, "--") {
				listed = append(listed, field)

				break
			}
		}
	}

	rank := map[string]int{}
	for index, flag := range helpOptionOrder {
		rank[flag] = index
	}

	previous := -1
	for _, flag := range listed {
		at, ok := rank[flag]
		if !ok {
			t.Errorf("%s is in OPTIONS but not in helpOptionOrder, so its position is arbitrary", flag)

			continue
		}
		if at < previous {
			t.Errorf("%s is out of order in OPTIONS", flag)
		}
		previous = at
	}

	for _, required := range []string{"-v, --version", "-h, --help", "-j, --json"} {
		if !strings.Contains(options, required) {
			t.Errorf("OPTIONS is missing %q", required)
		}
	}
}

// --all is parsed at the root so `appwrite --all push` works, but it acts on
// push and pull. Documenting it globally would offer it on every command.
func TestAllIsHiddenAtTheRootAndDocumentedOnPush(t *testing.T) {
	root := NewRootCommand()

	if _, options, found := strings.Cut(RenderMainHelp(root), "\nOPTIONS\n"); found {
		if strings.Contains(options, "--all") {
			t.Error("--all is documented in the main screen's OPTIONS")
		}
	}

	push := resolveCommand(root, "push")
	if push == nil {
		t.Fatal("`push` is missing")
	}

	flag := push.Flags().Lookup("all")
	if flag == nil {
		t.Fatal("`push` does not define --all")
	}
	if flag.Hidden {
		t.Error("--all is hidden on `push`, where it is the flag users need")
	}
	if flag.Shorthand != "a" {
		t.Errorf("`push --all` shorthand = %q, want \"a\"", flag.Shorthand)
	}
}

// The help function is inherited, so overriding it on the root would replace it
// for all 600-odd subcommands -- where cobra's own listing is the right one.
func TestSubcommandHelpIsStillCobras(t *testing.T) {
	root := NewRootCommand()
	users := resolveCommand(root, "users")
	if users == nil {
		t.Skip("`users` is not in this spec")
	}

	buffer := &bytes.Buffer{}
	users.SetOut(buffer)
	users.Help()

	if !strings.Contains(buffer.String(), "Usage:") {
		t.Errorf("`users --help` is not cobra's screen:\n%s", buffer.String())
	}
	if strings.Contains(buffer.String(), "GET STARTED") {
		t.Error("`users --help` rendered the main help screen")
	}
}

func TestFooterTellsTheUserWhereToGoNext(t *testing.T) {
	screen := RenderMainHelp(NewRootCommand())

	want := "Run `appwrite <command> --help` for details on a specific command."
	if !strings.Contains(screen, want) {
		t.Errorf("the screen does not end with %q", want)
	}
}

// help.ts passes minColumnWidth 2 to commander's wrap rather than taking its
// default of 40, so the TypeScript screen keeps wrapping into a narrow terminal
// instead of emitting one line that runs off the edge. Verified against
// commander itself at screen widths 44, 39, 20 and 10.
func TestNarrowTerminalsStillWrap(t *testing.T) {
	// Screen width 20 -> 16 columns. commander produces 11 breaks here.
	narrow := wrapHelpText(theRealDescription, 20-helpGap-helpGap, helpIndent)
	if strings.Count(narrow, "\n") == 0 {
		t.Errorf("a 20-column terminal was not wrapped at all:\n%s", narrow)
	}
	for _, line := range strings.Split(narrow, "\n") {
		if !strings.HasPrefix(line, helpIndent) {
			t.Errorf("a wrapped line lost its indent: %q", line)
		}
	}

	// Below commander's floor of 2 columns it returns the string as it is, which
	// is also what stops a terminal reporting 3 columns from reaching the loop
	// with a negative width.
	for _, columns := range []int{1, 0, -4} {
		got := wrapHelpText(theRealDescription, columns, helpIndent)
		if got != helpIndent+theRealDescription {
			t.Errorf("at %d columns the paragraph was altered:\n%s", columns, got)
		}
	}

	// And the 80-column screen still wraps to the three lines the TypeScript
	// prints.
	if wide := wrapHelpText(theRealDescription, helpMaxWidth-helpGap-helpGap, helpIndent); strings.Count(wide, "\n") != 2 {
		t.Errorf("the 80-column screen no longer wraps to three lines:\n%s", wide)
	}
}

// commander's wrap is not a plain greedy word wrap, and the difference is
// visible on the screen: a line holds columnWidth-1 characters rather than
// columnWidth, and the first line carries two characters that were never
// measured because commander wraps `str.slice(indent)` and prefixes
// `str.slice(0, indent)` back on.
//
// A from-scratch greedy wrap agreed at 80 columns and disagreed at 15, 17, 18,
// 30, 41, 42, 49, 50, 56, 57, 62 and 71 -- which is why this compares against
// captured commander output rather than against a rule.
//
// Captured by calling commander's own Help.wrap with the arguments help.ts
// passes it: `wrap(description, width - 2, 2, 2)`, prefixed with the two-space
// indent, for every screen width from 6 to 80. The spread below is the boundary,
// the cap, the floor, and every width the greedy version got wrong.
var commanderWrapped = map[int]string{
	80: "  Appwrite is an open-source self-hosted backend server that abstracts and\n  simplifies complex and repetitive development tasks behind a very simple\n  REST API",
	71: "  Appwrite is an open-source self-hosted backend server that abstracts\n  and simplifies complex and repetitive development tasks behind a\n  very simple REST API",
	62: "  Appwrite is an open-source self-hosted backend server that\n  abstracts and simplifies complex and repetitive\n  development tasks behind a very simple REST API",
	57: "  Appwrite is an open-source self-hosted backend server\n  that abstracts and simplifies complex and repetitive\n  development tasks behind a very simple REST API",
	56: "  Appwrite is an open-source self-hosted backend server\n  that abstracts and simplifies complex and\n  repetitive development tasks behind a very simple\n  REST API",
	50: "  Appwrite is an open-source self-hosted backend\n  server that abstracts and simplifies complex\n  and repetitive development tasks behind a\n  very simple REST API",
	49: "  Appwrite is an open-source self-hosted backend\n  server that abstracts and simplifies complex\n  and repetitive development tasks behind a\n  very simple REST API",
	42: "  Appwrite is an open-source self-hosted\n  backend server that abstracts and\n  simplifies complex and repetitive\n  development tasks behind a very\n  simple REST API",
	41: "  Appwrite is an open-source self-hosted\n  backend server that abstracts and\n  simplifies complex and repetitive\n  development tasks behind a very\n  simple REST API",
	30: "  Appwrite is an open-source\n  self-hosted backend\n  server that abstracts and\n  simplifies complex and\n  repetitive development\n  tasks behind a very\n  simple REST API",
	20: "  Appwrite is an\n  open-source\n  self-hosted\n  backend server\n  that abstracts\n  and simplifies\n  complex and\n  repetitive\n  development\n  tasks behind a\n  very simple\n  REST API",
	18: "  Appwrite is an\n  open-source\n  self-hosted\n  backend\n  server that\n  abstracts and\n  simplifies\n  complex and\n  repetitive\n  development\n  tasks behind\n  a very simple\n  REST API",
	17: "  Appwrite is an\n  open-source\n  self-hosted\n  backend\n  server that\n  abstracts\n  and\n  simplifies\n  complex and\n  repetitive\n  development\n  tasks behind\n  a very\n  simple REST\n  API",
	15: "  Appwrite is\n  an\n  open-source\n  self-hosted\n  backend\n  server\n  that\n  abstracts\n  and\n  simplifies\n  complex\n  and\n  repetitive\n  development\n  tasks\n  behind a\n  very\n  simple\n  REST API",
	10: "  Appwrite\n  is an\n  open-source\n  self-hosted\n  backend\n  server\n  that\n  abstracts\n  and\n  simplifies\n  complex\n  and\n  repetitive\n  development\n  tasks\n  behind\n  a\n  very\n  simple\n  REST\n  API",
	6:  "  Appwrite\n  is\n  an\n  open-source\n  self-hosted\n  backend\n  server\n  that\n  abstracts\n  and\n  simplifies\n  complex\n  and\n  repetitive\n  development\n  tasks\n  behind\n  a\n  very\n  simple\n  REST\n  API",
}

func TestWrapMatchesCommanderAtEveryWidth(t *testing.T) {
	for width, want := range commanderWrapped {
		got := wrapHelpText(theRealDescription, width-helpGap-helpGap, helpIndent)
		if got != want {
			t.Errorf("width %d differs\n got:\n%s\nwant:\n%s", width, got, want)
		}
	}
}
