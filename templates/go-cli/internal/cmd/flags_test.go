package cmd

import (
	"testing"

	"github.com/spf13/cobra"
)

// A script written against the TypeScript CLI types `--no-code`. Answering it
// "unknown flag" fails a pipeline over spelling, so both forms are accepted.
func TestNegatedFlagTurnsItsPositiveOff(t *testing.T) {
	for _, spelling := range []string{"--no-code", "--code=false"} {
		code := true
		command := negatableCommand(&code)

		command.SetArgs([]string{spelling})
		if err := command.Execute(); err != nil {
			t.Fatalf("%s: %v", spelling, err)
		}
		if code {
			t.Errorf("%s left the flag on", spelling)
		}
	}
}

// The flag defaults on and stays on when nobody says otherwise.
func TestNegatableFlagDefaultsOn(t *testing.T) {
	code := true
	command := negatableCommand(&code)

	command.SetArgs(nil)
	if err := command.Execute(); err != nil {
		t.Fatal(err)
	}
	if !code {
		t.Error("the flag defaulted off")
	}
}

// `--no-code=false` is a double negative asking for the default. Reading it as
// "the flag named no-code was given, so turn code off" inverts the request.
func TestNegatedFlagSetToFalseLeavesItOn(t *testing.T) {
	code := true
	command := negatableCommand(&code)

	command.SetArgs([]string{"--no-code=false"})
	if err := command.Execute(); err != nil {
		t.Fatal(err)
	}
	if !code {
		t.Error("--no-code=false turned the flag off")
	}
}

// One flag, documented once. The alias exists for compatibility, not as a
// second way to spell the same option in the help output.
func TestNegatedFlagIsHiddenFromHelp(t *testing.T) {
	code := true
	command := negatableCommand(&code)

	if flag := command.Flags().Lookup("no-code"); flag == nil || !flag.Hidden {
		t.Error("--no-code should exist and be hidden")
	}
	if flag := command.Flags().Lookup("code"); flag == nil || flag.Hidden {
		t.Error("--code should exist and be documented")
	}
}

func negatableCommand(code *bool) *cobra.Command {
	command := &cobra.Command{
		Use: "probe",
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error { return nil },
	}
	negatableBool(command.Flags(), code, "code", "Push the code")

	return command
}
