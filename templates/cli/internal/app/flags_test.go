package app

import (
	"testing"

	"github.com/spf13/cobra"
)

func TestAnyFlagChangedReturnsTrueForChangedFlag(t *testing.T) {
	command := queryFlagCommand()
	command.SetArgs([]string{"--filter", "title=Bulk One"})

	if err := command.Execute(); err != nil {
		t.Fatal(err)
	}

	if !AnyFlagChanged(command, "queries", "filter", "missing") {
		t.Error("expected changed --filter to be reported")
	}
}

func TestAnyFlagChangedReturnsFalseWhenOnlyUnknownFlagsAreNamed(t *testing.T) {
	command := queryFlagCommand()
	command.SetArgs([]string{"--filter", "title=Bulk One"})

	if err := command.Execute(); err != nil {
		t.Fatal(err)
	}

	if AnyFlagChanged(command, "missing") {
		t.Error("unknown flags should be ignored")
	}
}

func queryFlagCommand() *cobra.Command {
	var filter []string
	var queries []string

	command := &cobra.Command{
		Use: "probe",
		RunE: func(command *cobra.Command, args []string) error {
			return nil
		},
	}
	command.Flags().StringArrayVar(&filter, "filter", nil, "Filter")
	command.Flags().StringArrayVar(&queries, "queries", nil, "Queries")

	return command
}

func TestNoArgsShowsHowToRepeatTheListFlagThatWasGiven(t *testing.T) {
	command := queryFlagCommand()
	command.Args = NoArgs
	command.SetArgs([]string{"--queries", `read("any")`, `update("users")`})

	err := command.Execute()
	want := `unexpected argument "update(\"users\")": --queries takes one value per flag. Repeat the flag for each value: --queries 'read("any")' --queries 'update("users")'`
	if err == nil || err.Error() != want {
		t.Errorf("Execute() = %v, want %s", err, want)
	}
}

func TestNoArgsListsEveryListFlagThatWasGiven(t *testing.T) {
	command := queryFlagCommand()
	command.Args = NoArgs
	command.SetArgs([]string{"--filter", "a=1", "--queries", "q", "stray"})

	err := command.Execute()
	want := `unexpected argument "stray": --filter, --queries take one value per flag. Repeat the flag for each value: --filter <value> --filter <value>`
	if err == nil || err.Error() != want {
		t.Errorf("Execute() = %v, want %s", err, want)
	}
}

func TestNoArgsRejectsArgumentsWhenNoListFlagWasGiven(t *testing.T) {
	command := queryFlagCommand()
	command.Args = NoArgs
	command.SetArgs([]string{"stray"})

	err := command.Execute()
	want := `unexpected argument "stray": probe takes no positional arguments`
	if err == nil || err.Error() != want {
		t.Errorf("Execute() = %v, want %s", err, want)
	}
}
