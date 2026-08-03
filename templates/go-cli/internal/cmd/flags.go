package cmd

import (
	"strconv"
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

// Optional booleans carry NoOptDefVal so that a bare `--enabled` means true,
// the way commander's `--enabled [value]` does. The cost is that pflag then
// refuses to consume a space-separated value: `--enabled false` sets the flag
// TRUE and drops `false` as a stray positional argument, so the request says
// the opposite of what was typed. A live run of
// `users update-email-verification --email-verification false` marked the
// account verified.
//
// pflag cannot express "optional value, either spelling" -- that ambiguity is
// why NoOptDefVal exists. So the value is joined to the flag before pflag sees
// it, and only when the following token is a boolean literal. `--strict ./out`
// keeps its positional; `--required false` does not silently invert.

// boolLiteral reports whether pflag would accept token as a boolean value.
func boolLiteral(token string) bool {
	_, err := strconv.ParseBool(token)

	return err == nil
}

// booleanFlag returns the boolean flag a token names, or nil.
//
// A token that already carries `=` is left alone: pflag parses it correctly and
// rewriting it would corrupt values containing an equals sign.
func booleanFlag(command *cobra.Command, token string) *pflag.Flag {
	if strings.Contains(token, "=") {
		return nil
	}

	var flag *pflag.Flag
	switch {
	case strings.HasPrefix(token, "--"):
		name := strings.TrimPrefix(token, "--")
		if name == "" {
			return nil
		}
		flag = command.Flags().Lookup(name)
		if flag == nil {
			flag = command.InheritedFlags().Lookup(name)
		}
	case len(token) == 2 && token[0] == '-':
		flag = command.Flags().ShorthandLookup(token[1:])
		if flag == nil {
			flag = command.InheritedFlags().ShorthandLookup(token[1:])
		}
	default:
		return nil
	}

	if flag == nil || flag.Value.Type() != "bool" {
		return nil
	}

	return flag
}

// RewriteBooleanValues joins `--flag value` into `--flag=value` for boolean
// flags, so the space-separated spelling means what the TypeScript CLI means.
//
// Runs before cobra parses. Find matches on command names only, ignoring
// flags, so the target command -- and therefore which of its flags are boolean
// -- is known without parsing anything.
func RewriteBooleanValues(root *cobra.Command, args []string) []string {
	target, _, err := root.Find(args)
	if err != nil || target == nil {
		return args
	}

	rewritten := make([]string, 0, len(args))
	for index := 0; index < len(args); index++ {
		token := args[index]

		// Everything after `--` is positional by definition.
		if token == "--" {
			rewritten = append(rewritten, args[index:]...)

			break
		}

		if index+1 < len(args) && boolLiteral(args[index+1]) &&
			booleanFlag(target, token) != nil {
			rewritten = append(rewritten, token+"="+args[index+1])
			index++

			continue
		}

		rewritten = append(rewritten, token)
	}

	return rewritten
}

// negatableBool registers name (defaulting on) together with its `--no-name`
// spelling.
func negatableBool(flags *pflag.FlagSet, target *bool, name, usage string) {
	flags.BoolVar(target, name, true, usage)

	flags.Bool("no-"+name, false, negativeUsage(usage))
	negative := flags.Lookup("no-" + name)
	// Bare `--no-code`, with no value, is how commander spells it.
	negative.NoOptDefVal = "true"
}

// negativeUsage describes the `--no-x` form the way commander describes it.
//
// Documented rather than hidden: the TypeScript lists these in --help, so
// hiding them meant a user reading Go's help could not discover the spelling
// their existing scripts already use.
func negativeUsage(usage string) string {
	return "Don't " + strings.ToLower(usage[:1]) + strings.TrimSuffix(usage[1:], ".")
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
