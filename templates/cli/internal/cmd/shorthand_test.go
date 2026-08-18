package cmd

import (
	"bytes"
	"strings"
	"testing"

	"github.com/spf13/cobra"
	"github.com/spf13/pflag"
)

// pflag panics on a shorthand collision, and cobra merges root persistent flags
// into a subcommand lazily -- so a collision stays invisible until someone runs
// the one command that has it. This forces the merge on every command.
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

func TestNoArgCommandsRejectExtraArguments(t *testing.T) {
	tests := [][]string{
		{"users", "definitely-not-a-command"},
		{"users", "list", "definitely-not-an-argument"},
		{"pull", "definitely-not-a-command"},
		{"push", "definitely-not-a-command"},
	}

	for _, args := range tests {
		t.Run(strings.Join(args, " "), func(t *testing.T) {
			root := NewRootCommand()
			root.SetArgs(args)
			root.SetOut(&bytes.Buffer{})
			root.SetErr(&bytes.Buffer{})

			err := root.Execute()
			if err == nil {
				t.Fatal("command accepted an unexpected positional argument")
			}
			if !strings.Contains(err.Error(), "unknown command") {
				t.Fatalf("error = %q, want an unknown command error", err)
			}
		})
	}
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
