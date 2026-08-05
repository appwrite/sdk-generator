package cmd

import (
	"strings"
	"testing"

	"github.com/spf13/cobra"
	"github.com/spf13/pflag"
)

// A root persistent flag is merged into every subcommand's flagset, and pflag
// PANICS on a shorthand collision rather than returning an error. cobra does that
// merge lazily -- on execute, or on the first help render -- so a collision is
// invisible until someone runs the one command that has it.
//
// That is exactly how `-a` for --all shipped: it was registered persistently on
// the root, `push table` and `push collection` define their own `-a` for
// --attempts, and both crashed with
//
//	panic: unable to redefine 'a' shorthand in "table" flagset
//
// while every other command worked and every test passed.
//
// This walks the whole tree and forces the merge on each command, so the next one
// fails here instead of in a user's terminal.
func TestNoCommandHasAShorthandCollision(t *testing.T) {
	root := NewRootCommand()

	var walk func(command *cobra.Command)
	walk = func(command *cobra.Command) {
		func() {
			defer func() {
				if recovered := recover(); recovered != nil {
					t.Errorf("`%s` panics when its flags are merged: %v",
						commandPath(command), recovered)
				}
			}()

			// What cobra does on execute and on `--help`: pull the persistent
			// flags down into this command's own set.
			command.InitDefaultHelpFlag()
			command.Flags().VisitAll(func(*pflag.Flag) {})
		}()

		for _, child := range command.Commands() {
			walk(child)
		}
	}

	walk(root)
}

// commandPath is cobra's CommandPath without the binary name, which makes a
// failure read as the command a user would type.
func commandPath(command *cobra.Command) string {
	path := command.CommandPath()
	if index := strings.IndexByte(path, ' '); index >= 0 {
		return path[index+1:]
	}

	return path
}
