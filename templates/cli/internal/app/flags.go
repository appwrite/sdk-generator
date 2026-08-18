package app

import "github.com/spf13/cobra"

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
