package schema

import (
	"fmt"
	"io"
	"strings"
)

// Ports drawTable (templates/cli/lib/parser.ts) for the two tables this package
// shows before asking for approval.
//
// Human-readable output is explicitly outside the parity contract
// (docs/go-cli/PLAN.md §3), so this is a plain aligned grid rather than a port
// of cli-table3's box drawing. What IS reproduced is the column set and the
// wording of each cell: those are what tells a user their `title` column is
// about to be dropped and rebuilt.

// printTable writes an aligned grid.
//
// A cell containing newlines -- an attribute with several changed fields --
// prints one line per entry with the other columns blank, so rows stay readable.
func printTable(writer io.Writer, headers []string, rows [][]string) {
	widths := make([]int, len(headers))
	for index, header := range headers {
		widths[index] = len([]rune(header))
	}
	for _, row := range rows {
		for index, cell := range row {
			for _, line := range strings.Split(cell, "\n") {
				widths[index] = max(widths[index], len([]rune(line)))
			}
		}
	}

	write := func(cells []string) {
		lines := 1
		split := make([][]string, len(cells))
		for index, cell := range cells {
			split[index] = strings.Split(cell, "\n")
			lines = max(lines, len(split[index]))
		}

		for line := range lines {
			parts := make([]string, len(cells))
			for index := range cells {
				value := ""
				if line < len(split[index]) {
					value = split[index][line]
				}
				parts[index] = value + strings.Repeat(" ", widths[index]-len([]rune(value)))
			}
			fmt.Fprintf(writer, "  %s\n", strings.TrimRight(strings.Join(parts, "  "), " "))
		}
	}

	fmt.Fprintln(writer)
	write(headers)
	for _, row := range rows {
		write(row)
	}
	fmt.Fprintln(writer)
}

// printChanges renders the attribute change table.
func printChanges(writer io.Writer, changes []Change) {
	rows := make([][]string, 0, len(changes))
	for _, entry := range changes {
		rows = append(rows, []string{entry.Key, entry.Action, entry.Reason})
	}

	printTable(writer, []string{"Key", "Action", "Reason"}, rows)
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
