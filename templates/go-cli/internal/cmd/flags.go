package cmd

import (
	"strings"

	"github.com/spf13/cobra"
	"github.com/spf13/pflag"
)

// Commander declares flags like `--no-code` and `--no-logs`: booleans that
// default ON and are turned off by naming them. pflag has no negation, so this
// port spells the positive flag and defaults it to true -- `--code=false`.
//
// That is a reasonable Go spelling and a poor migration. A script written
// against the TypeScript CLI types `--no-code`, and answering it "unknown flag"
// turns a working pipeline into a failing one over spelling. BOTH ARE
// ACCEPTED. The negative form is hidden so `--help` documents one flag rather
// than two ways to say the same thing.

// negatableBool registers name (defaulting on) together with its `--no-name`
// spelling.
func negatableBool(flags *pflag.FlagSet, target *bool, name, usage string) {
	flags.BoolVar(target, name, true, usage)

	flags.Bool("no-"+name, false, "Alias for --"+name+"=false")
	negative := flags.Lookup("no-" + name)
	// Bare `--no-code`, with no value, is how commander spells it.
	negative.NoOptDefVal = "true"
	negative.Hidden = true
}

// applyNegatedFlags folds every `--no-x` the user typed into its positive flag.
//
// Runs before the command body so it reads one field rather than a flag and its
// negation. Visit walks only the flags actually given, so a `--no-x` left alone
// cannot override an explicit `--x=true`.
func applyNegatedFlags(command *cobra.Command) error {
	flags := command.Flags()

	var failure error
	flags.Visit(func(flag *pflag.Flag) {
		positive, negated := strings.CutPrefix(flag.Name, "no-")
		// `--no-x=false` is a double negative that asks for the default, which
		// is what the flag already holds.
		if !negated || flag.Value.String() != "true" {
			return
		}
		if flags.Lookup(positive) == nil {
			return
		}

		if err := flags.Set(positive, "false"); err != nil && failure == nil {
			failure = err
		}
	})

	return failure
}
