package cmd

import (
	"os"

	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/spf13/cobra"
)

// Ports the global flags in templates/cli/lib/parser.ts:37 (`cliConfig`).
//
// The TypeScript keeps these in a module-level singleton. Here they live on one
// struct hung off the root command, because a package-level global is the thing
// that makes commands impossible to test in isolation.

// Globals are the flags every command honours.
type Globals struct {
	JSON        bool
	Raw         bool
	ShowSecrets bool
	Verbose     bool
	Force       bool
	All         bool
}

var globals = &Globals{}

// registerGlobalFlags adds the persistent flags to the root command.
func registerGlobalFlags(root *cobra.Command) {
	flags := root.PersistentFlags()
	flags.BoolVarP(&globals.JSON, "json", "j", false, "Output the response as JSON.")
	flags.BoolVarP(&globals.Raw, "raw", "R", false, "Output the unfiltered response as JSON.")
	flags.BoolVar(&globals.ShowSecrets, "show-secrets", false,
		"Show secret values in full instead of masking them.")
	flags.BoolVar(&globals.Verbose, "verbose", false, "Show detailed output for debugging.")
	flags.BoolVarP(&globals.Force, "force", "f", false, "Skip confirmation prompts.")
	flags.BoolVar(&globals.All, "all", false, "Apply the command to every matching resource.")
}

// renderer builds an output renderer from the current global flags.
//
// --raw wins over --json when both are given: raw is the less filtered of the
// two, so honouring it loses no information.
func renderer() *output.Renderer {
	mode := output.ModeTable
	switch {
	case globals.Raw:
		mode = output.ModeRaw
	case globals.JSON:
		mode = output.ModeJSON
	}

	return &output.Renderer{
		Mode:        mode,
		ShowSecrets: globals.ShowSecrets,
		Writer:      os.Stdout,
	}
}

// render writes an SDK result through the configured renderer.
//
// The SDK returns typed models; they are round-tripped through JSON so the
// output path sees the same ordered shape as the API sent, rather than Go's
// struct field order.
func render(value any) error {
	encoded, err := output.MarshalOrdered(value)
	if err != nil {
		return err
	}

	decoded, err := output.DecodeOrdered(encoded)
	if err != nil {
		return err
	}

	return renderer().Render(decoded)
}
