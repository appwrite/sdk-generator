package app

import (
	"os"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/spf13/cobra"
)

// Package app holds the process-wide CLI state the generated commands need.
//
// It exists to break a cycle: internal/cmd registers the generated commands, so
// the generated package cannot import internal/cmd to reach the global flags or
// the renderer. Both sides import this instead.

// Globals are the flags every command honours.
type Globals struct {
	JSON        bool
	Raw         bool
	ShowSecrets bool
	Verbose     bool
	Force       bool
	All         bool
	Report      bool
}

var globals = &Globals{}

// Flags exposes the parsed global flags.
func Flags() *Globals { return globals }

// AllPointer exposes --all for binding a second, shorthand-carrying definition.
//
// `push` and `pull` declare their own -a and must write the same variable the
// persistent --all writes, or the two spellings would disagree.
func (g *Globals) AllPointer() *bool { return &g.All }

// RegisterGlobalFlags adds the persistent flags to the root command.
func RegisterGlobalFlags(root *cobra.Command) {
	// These descriptions are public because `--help` prints them.
	flags := root.PersistentFlags()
	flags.BoolVarP(&globals.JSON, "json", "j", false, "Output filtered JSON (empty values omitted)")
	flags.BoolVarP(&globals.Raw, "raw", "R", false, "Output the full raw JSON response")
	flags.BoolVar(&globals.ShowSecrets, "show-secrets", false,
		"Reveal secrets and tokens in output (redacted by default)")
	flags.BoolVarP(&globals.Verbose, "verbose", "V", false, "Show full error stack traces")
	flags.BoolVarP(&globals.Force, "force", "f", false, "Skip confirmation prompts")
	// --all is persistent so every command that honours it reads one value, but
	// WITHOUT the -a shorthand. cobra merges a root persistent flag into every
	// subcommand, and `push table` and `push collection` define their own -a for
	// --attempts -- pflag panics on a shorthand collision, so registering -a here
	// crashed those two commands outright. The shorthand is added locally on
	// `push` and `pull`, which is where the TypeScript has it (push.ts:4272,
	// pull.ts:1121); commander options do not inherit, so it never had to choose.
	//
	// Hidden at the root for the same reason it is hidden there in the
	// TypeScript (`new Option('-a, --all', ...).hideHelp()`): it is parsed
	// globally so `appwrite --all push` and `appwrite --all init skill` keep
	// working, but it is documented only on commands where it selects resources
	// or skills.
	flags.BoolVar(&globals.All, "all", false, "Select every applicable resource or skill")
	if flag := flags.Lookup("all"); flag != nil {
		flag.Hidden = true
	}
	flags.BoolVar(&globals.Report, "report", false,
		"Print a prefilled bug report link on error")
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
