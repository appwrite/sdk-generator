package app

import (
	"fmt"
	"strings"

	"github.com/spf13/cobra"
	"github.com/spf13/pflag"
)

// Pointer helpers for optional query flags.
//
// internal/query distinguishes "not requested" from "requested as zero" with a
// nil pointer, because `--limit 0` and no --limit at all are different requests.
// cobra reports which flags the user actually typed, so that is the signal.

// FlagInt returns a pointer to value when the flag was set, nil otherwise.
func FlagInt(command *cobra.Command, name string, value int) *int {
	if !command.Flags().Changed(name) {
		return nil
	}

	return &value
}

// FlagString returns a pointer to value when the flag was set, nil otherwise.
func FlagString(command *cobra.Command, name string, value string) *string {
	if !command.Flags().Changed(name) {
		return nil
	}

	return &value
}

// AnyFlagChanged reports whether any registered flag in names was set by the
// user. Unknown names are ignored so generated call sites can share one query
// flag list across commands with different query surfaces.
func AnyFlagChanged(command *cobra.Command, names ...string) bool {
	for _, name := range names {
		if flag := command.Flags().Lookup(name); flag != nil && command.Flags().Changed(name) {
			return true
		}
	}

	return false
}

func NoArgs(command *cobra.Command, args []string) error {
	if len(args) == 0 {
		return nil
	}

	unexpected := fmt.Sprintf("unexpected argument %q", args[0])

	var given []*pflag.Flag
	command.Flags().Visit(func(flag *pflag.Flag) {
		if flag.Value.Type() == "stringArray" {
			given = append(given, flag)
		}
	})

	if len(given) == 1 {
		flag := given[0]
		values := append(flag.Value.(pflag.SliceValue).GetSlice(), args...)
		repeated := make([]string, 0, len(values))
		for _, value := range values {
			repeated = append(repeated, "--"+flag.Name+" "+shellArgument(value))
		}

		return fmt.Errorf("%s: --%s takes one value per flag. Repeat the flag for each value: %s", unexpected, flag.Name, strings.Join(repeated, " "))
	}
	if len(given) == 0 {
		return fmt.Errorf("%s: %s takes no positional arguments", unexpected, command.CommandPath())
	}

	names := make([]string, 0, len(given))
	for _, flag := range given {
		names = append(names, "--"+flag.Name)
	}

	return fmt.Errorf("%s: %s take one value per flag. Repeat the flag for each value: --%s <value> --%s <value>", unexpected, strings.Join(names, ", "), given[0].Name, given[0].Name)
}

func shellArgument(value string) string {
	if value != "" {
		safe := true
		for _, character := range value {
			if !(character >= 'a' && character <= 'z') &&
				!(character >= 'A' && character <= 'Z') &&
				!(character >= '0' && character <= '9') &&
				!strings.ContainsRune("-._", character) {
				safe = false
				break
			}
		}
		if safe {
			return value
		}
	}

	return "'" + strings.ReplaceAll(value, "'", "'\"'\"'") + "'"
}
