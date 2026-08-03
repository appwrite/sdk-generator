package app

import (
	"os"

	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/appwrite/appwrite-cli-go/internal/sdk"
	"github.com/spf13/cobra"
)

// Package app holds the process-wide CLI state the generated commands need.
//
// It exists to break a cycle: internal/cmd registers the generated commands, so
// the generated package cannot import internal/cmd to reach the global flags or
// the renderer. Both sides import this instead.
//
// Ports the `cliConfig` singleton in templates/cli/lib/parser.ts:37.

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

// Flags exposes the parsed global flags.
func Flags() *Globals { return globals }

// RegisterGlobalFlags adds the persistent flags to the root command.
func RegisterGlobalFlags(root *cobra.Command) {
	flags := root.PersistentFlags()
	flags.BoolVarP(&globals.JSON, "json", "j", false, "Output the response as JSON.")
	flags.BoolVarP(&globals.Raw, "raw", "R", false, "Output the unfiltered response as JSON.")
	flags.BoolVar(&globals.ShowSecrets, "show-secrets", false,
		"Show secret values in full instead of masking them.")
	flags.BoolVar(&globals.Verbose, "verbose", false, "Show detailed output for debugging.")
	flags.BoolVarP(&globals.Force, "force", "f", false, "Skip confirmation prompts.")
	flags.BoolVar(&globals.All, "all", false, "Apply the command to every matching resource.")
}

// Renderer builds an output renderer from the current global flags.
//
// --raw wins over --json when both are given: raw is the less filtered of the
// two, so honouring it loses no information.
func Renderer() *output.Renderer {
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

// Render writes an SDK result through the configured renderer.
//
// The response the API sent is preferred over the typed struct. --raw promises
// "the full raw JSON response" and --json filters that response, so rendering
// the struct instead silently dropped every field the generated model does not
// declare. The struct is the fallback for commands that produce a result
// without a request behind it.
//
// Either way the bytes are decoded through DecodeOrdered so the output path
// sees the API's key order rather than Go's struct field order.
func Render(value any) error {
	return render(Renderer(), value)
}

// render is Render against an explicit renderer, so it can be exercised without
// the process-wide global flags.
func render(renderer *output.Renderer, value any) error {
	encoded := sdk.LastResponse.Take()

	if len(encoded) == 0 {
		marshalled, err := output.MarshalOrdered(value)
		if err != nil {
			return err
		}
		encoded = marshalled
	}

	decoded, err := output.DecodeOrdered(encoded)
	if err != nil {
		return err
	}

	return renderer.Render(decoded)
}
