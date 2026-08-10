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
