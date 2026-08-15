package cmd

import (
	"testing"

	"github.com/spf13/cobra"
)

// An existing script types `--no-code`. Answering it "unknown flag" fails a
// pipeline over spelling, so both forms are accepted.
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

// Both spellings are documented. Hiding the negation meant someone reading
// --help could not discover the spelling their existing scripts already use.
func TestBothFlagSpellingsAreDocumented(t *testing.T) {
	code := true
	command := negatableCommand(&code)

	for _, name := range []string{"code", "no-code"} {
		flag := command.Flags().Lookup(name)
		if flag == nil {
			t.Errorf("--%s does not exist", name)

			continue
		}
		if flag.Hidden {
			t.Errorf("--%s is hidden from help", name)
		}
		if flag.Usage == "" {
			t.Errorf("--%s has no description", name)
		}
	}

	if got := command.Flags().Lookup("no-code").Usage; got != "Don't push the code" {
		t.Errorf("--no-code usage = %q", got)
	}
}

// `--enabled false` must mean false. pflag gives a NoOptDefVal flag the default
// and drops the value as a positional, so this asked the API to VERIFY an
// account whose operator typed `--email-verification false`.
func TestBooleanFlagTakesASpaceSeparatedValue(t *testing.T) {
	for _, spelling := range [][]string{
		{"--verified", "false"},
		{"--verified=false"},
		{"-v", "false"},
	} {
		verified, _, root := booleanCommand()

		root.SetArgs(RewriteBooleanValues(root, append([]string{"probe"}, spelling...)))
		if err := root.Execute(); err != nil {
			t.Fatalf("%v: %v", spelling, err)
		}
		if *verified {
			t.Errorf("%v set the flag true", spelling)
		}
	}
}

// `push site --activate true` is registered as a string optional flag because
// it accepts the yes/no forms. It still needs the same rewrite, or true
// is rejected as a stray positional argument.
func TestStringOptionalBooleanFlagTakesASpaceSeparatedValue(t *testing.T) {
	_, activate, root := booleanCommand()

	root.SetArgs(RewriteBooleanValues(root, []string{"probe", "--activate", "yes"}))
	if err := root.Execute(); err != nil {
		t.Fatal(err)
	}
	if *activate != "yes" {
		t.Errorf("activate = %q", *activate)
	}
}

// The bare spelling still means true -- that is what NoOptDefVal is for, and
// An optional-value flag behaves the same way.
func TestBareBooleanFlagIsStillTrue(t *testing.T) {
	verified, _, root := booleanCommand()

	root.SetArgs(RewriteBooleanValues(root, []string{"probe", "--verified"}))
	if err := root.Execute(); err != nil {
		t.Fatal(err)
	}
	if !*verified {
		t.Error("a bare --verified did not set the flag")
	}
}

// Only a boolean literal is claimed. A path following a boolean flag is a
// positional argument, and joining it would break `types --strict ./out`.
func TestBooleanRewriteLeavesPositionalsAlone(t *testing.T) {
	_, _, root := booleanCommand()

	got := RewriteBooleanValues(root, []string{"probe", "--verified", "./out"})
	want := []string{"probe", "--verified", "./out"}

	if len(got) != len(want) || got[1] != want[1] || got[2] != want[2] {
		t.Errorf("rewrote to %v, want %v", got, want)
	}
}

// A non-boolean flag's value must never be joined, or `--name false` would
// become a flag nobody registered.
func TestBooleanRewriteIgnoresNonBooleanFlags(t *testing.T) {
	_, _, root := booleanCommand()

	got := RewriteBooleanValues(root, []string{"probe", "--name", "false"})
	if len(got) != 3 || got[1] != "--name" || got[2] != "false" {
		t.Errorf("rewrote a string flag to %v", got)
	}
}

// Everything after `--` is positional, including a word that looks like a flag.
func TestBooleanRewriteStopsAtTheTerminator(t *testing.T) {
	_, _, root := booleanCommand()

	got := RewriteBooleanValues(root, []string{"probe", "--", "--verified", "false"})
	if len(got) != 4 || got[2] != "--verified" || got[3] != "false" {
		t.Errorf("rewrote past the terminator: %v", got)
	}
}

// booleanCommand builds a root with one subcommand carrying an optional
// boolean, registered the way the generated service commands register theirs.
func booleanCommand() (*bool, *string, *cobra.Command) {
	var (
		verified bool
		activate string
		name     string
	)

	probe := &cobra.Command{
		Use:  "probe",
		Args: cobra.ArbitraryArgs,
		RunE: func(command *cobra.Command, args []string) error { return nil },
	}
	probe.Flags().BoolVarP(&verified, "verified", "v", false, "Verified")
	probe.Flags().Lookup("verified").NoOptDefVal = "true"
	probe.Flags().StringVar(&activate, "activate", "", "Activate")
	probe.Flags().Lookup("activate").NoOptDefVal = "true"
	probe.Flags().StringVar(&name, "name", "", "Name")

	root := &cobra.Command{Use: "appwrite", SilenceUsage: true, SilenceErrors: true}
	root.AddCommand(probe)

	return &verified, &activate, root
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
