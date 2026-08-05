package schema

import (
	"fmt"
	"io"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// Ports drawTable (templates/cli/lib/parser.ts) for the two tables this package
// shows before asking for approval.
//
// Human-readable output is explicitly outside the parity contract, so this is
// a plain aligned grid rather than a port
// of cli-table3's box drawing. What IS reproduced is the column set and the
// wording of each cell: those are what tells a user their `title` column is
// about to be dropped and rebuilt.

// printChanges renders the attribute change table.
func printChanges(writer io.Writer, changes []Change) {
	rows := make([][]string, 0, len(changes))
	for _, entry := range changes {
		rows = append(rows, []string{entry.Key, entry.Action, entry.Reason})
	}

	fmt.Fprintf(writer, "\n%s\n\n", output.RenderTable([]string{"Key", "Action", "Reason"}, rows))
}

// printBanner writes one of the boxed data-loss warnings.
//
// Ports the console.log blocks at attributes.ts:893 and :904. They are shouted
// rather than logged because the confirmation that follows is the last point at
// which a column's data can be saved.
func printBanner(writer io.Writer, message string) {
	rule := strings.Repeat("-", len([]rune(message))+4)
	fmt.Fprintf(writer, "%s\n| %s |\n%s\n\n", rule, message, rule)
}
