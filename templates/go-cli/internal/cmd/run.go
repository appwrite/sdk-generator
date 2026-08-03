package cmd

import (
	"context"
	"encoding/json"
	"fmt"
	"os"
	"os/signal"
	"path/filepath"
	"strings"
	"syscall"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/docker"
	"github.com/appwrite/appwrite-cli-go/internal/dotenv"
	"github.com/appwrite/appwrite-cli-go/internal/ignore"
	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/appwrite/appwrite-cli-go/internal/watch"
	"github.com/spf13/cobra"
)

// Ports templates/cli/lib/commands/run.ts.
//
// `appwrite run function` builds a function in its open-runtimes image, starts
// it, and rebuilds or hot-swaps it as the sources change. Nothing here talks to
// the Appwrite API except the optional remote-variable fetch and the JWT setup,
// both of which degrade to a warning.

// portSearchStart and portSearchEnd bound the automatic port search.
const (
	portSearchStart = 3000
	portSearchEnd   = 3100
)

func newRunCommand() *cobra.Command {
	command := &cobra.Command{
		Use:   "run",
		Short: "Run project resources locally",
		RunE: func(command *cobra.Command, args []string) error {
			return command.Help()
		},
	}

	command.AddCommand(newRunFunctionCommand())

	return command
}

func newRunFunctionCommand() *cobra.Command {
	var (
		functionID    string
		port          int
		userID        string
		withVariables bool
		reload        bool
	)

	command := &cobra.Command{
		Use:     "function",
		Aliases: []string{"functions"},
		Short:   "Run functions in the current directory.",
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error {
			return runFunction(command, runOptions{
				FunctionID:    functionID,
				Port:          port,
				UserID:        userID,
				WithVariables: withVariables,
				Reload:        reload,
			})
		},
	}

	command.Flags().StringVar(&functionID, "function-id", "", "ID of function to run")
	command.Flags().IntVar(&port, "port", 0, "Local port")
	command.Flags().StringVar(&userID, "user-id", "", "ID of user to impersonate")
	command.Flags().BoolVar(&withVariables, "with-variables", false,
		"Run with function variables from function settings")
	negatableBool(command.Flags(), &reload, "reload",
		"Live reload the server when function files change")

	return command
}

type runOptions struct {
	FunctionID    string
	Port          int
	UserID        string
	WithVariables bool
	Reload        bool
}

func runFunction(command *cobra.Command, options runOptions) error {
	out := command.OutOrStdout()

	local, err := config.LoadLocal(config.LocalPath("."))
	if err != nil {
		return err
	}

	function, err := selectFunction(command, local, options.FunctionID)
	if err != nil {
		return err
	}

	tool, ok := docker.Tool(function.RuntimeName())
	if !ok {
		return fmt.Errorf("unknown runtime '%s'", function.Runtime)
	}

	port, err := resolvePort(options.Port)
	if err != nil {
		return err
	}

	client := &docker.Client{Stdout: out, Stderr: command.ErrOrStderr()}
	if !client.Available() {
		return fmt.Errorf(
			"Docker Engine is required for local development. " +
				"Please install Docker using: https://docs.docker.com/engine/install/")
	}

	printSettings(command, function)

	if err := docker.AssertSource(local, function); err != nil {
		return err
	}

	emulator := docker.NewEmulator(client, local, function)

	// A previous run may have left a container and a stale bundle behind.
	// Ignored rather than fatal: nothing to clean up is the common case.
	_ = emulator.Cleanup(context.Background())

	if err := emulator.PrepareScratch(); err != nil {
		return err
	}

	ctx, stop := signal.NotifyContext(command.Context(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	keys, variables := collectVariables(command, local, function, options)

	// The credentials collectVariables minted last an hour. A run outliving
	// them keeps serving, so the warning is the only sign that its API calls
	// have started failing.
	defer warnOnCredentialExpiry(out)()

	// Announced before it starts. A runtime image is hundreds of megabytes
	// and `docker pull` runs with its output piped, so without this line the
	// CLI sits silent for however long the download takes and reads as hung.
	// Ports the log in dockerPull (emulation/docker.ts:188).
	output.Log(out, "Verifying Docker image ...")
	if err := client.Pull(ctx, docker.ImageName(function)); err != nil {
		return err
	}

	queue := docker.NewQueue()
	if options.Reload {
		watcher, err := watch.Start(emulator.Directory, sourceMatcher(local, function), queue.Push)
		if err != nil {
			return err
		}
		defer watcher.Close()
	}

	// Locked across the first build so an edit made while it runs is held and
	// replayed once, rather than racing the initial start.
	queue.Lock()

	output.Log(out, "Building function using Docker ...")
	cancelled, err := emulator.Build(ctx, keys, variables, func() bool { return !queue.Empty() })
	if err != nil {
		return err
	}
	if cancelled {
		queue.Unlock()

		return nil
	}

	command.Println()
	output.Log(out, "Starting function using Docker ...")
	output.Log(out, "Function automatically restarts when you edit your code.")
	command.Println()

	if _, err := emulator.Start(ctx, port, keys, variables); err != nil {
		return err
	}

	command.Println()
	output.Success(out, "Visit http://localhost:%d/ to execute your function.", port)
	command.Println()

	queue.Unlock()

	return serve(ctx, command, emulator, tool, queue, port, keys, variables)
}

// serve reloads on every debounced change until the context is cancelled.
func serve(
	ctx context.Context,
	command *cobra.Command,
	emulator *docker.Emulator,
	tool docker.SystemTool,
	queue *docker.Queue,
	port int,
	keys []string,
	variables map[string]string,
) error {
	out := command.OutOrStdout()

	for {
		select {
		case <-ctx.Done():
			output.Log(out, "Cleaning up ...")
			// A fresh context: the one above is already cancelled, and
			// cleanup still has to reach Docker.
			_ = emulator.Cleanup(context.WithoutCancel(ctx))
			output.Success(out, "Local function successfully stopped.")

			return nil

		case files := <-queue.Events():
			queue.Lock()

			if err := reloadOnce(ctx, command, emulator, tool, queue, port, keys, variables, files); err != nil {
				output.Failure(command.ErrOrStderr(), "Failed to reload function with error: %v", err)
			}

			queue.Unlock()
		}
	}
}

// reloadOnce rebuilds or hot-swaps, then restarts.
func reloadOnce(
	ctx context.Context,
	command *cobra.Command,
	emulator *docker.Emulator,
	tool docker.SystemTool,
	queue *docker.Queue,
	port int,
	keys []string,
	variables map[string]string,
	files []string,
) error {
	out := command.OutOrStdout()

	emulator.Client.Stop(ctx, emulator.Function.ID)

	if err := docker.AssertSource(emulator.Local, emulator.Function); err != nil {
		return err
	}

	// A compiled runtime, or a change to a file the build step consumes, needs
	// the build re-run. Everything else can have its sources swapped into the
	// existing bundle, which is the difference between a second and a minute.
	if tool.Compiled || docker.IsDependencyChange(tool, files) {
		output.Log(out, "Rebuilding the function due to file changes ...")

		cancelled, err := emulator.Build(ctx, keys, variables, func() bool { return !queue.Empty() })
		if err != nil {
			return err
		}
		if cancelled {
			return nil
		}
	} else {
		output.Log(out, "Hot-swapping function.. Files with change are %s", strings.Join(files, ", "))

		if err := emulator.HotSwap(); err != nil {
			return err
		}
	}

	_, err := emulator.Start(ctx, port, keys, variables)

	return err
}

// sourceMatcher is the watcher's ignore predicate.
//
// Built from the same rules the build uses, plus code.tar.gz -- which the
// runtime image writes into the function directory and which would otherwise
// trigger a reload of the build that just produced it.
func sourceMatcher(local *config.Local, function config.Function) watch.Ignored {
	matcher := ignore.New().Add(docker.AppwriteDirectory).Add("code.tar.gz")

	if !function.Ignore.IsEmpty() {
		matcher.AddAll(function.Ignore.Rules())
	} else {
		directory := local.ResolveResourcePath("functions", function.Path)
		if contents, err := os.ReadFile(filepath.Join(directory, ".gitignore")); err == nil {
			matcher.Add(string(contents))
		}
	}

	return watch.PrefixIgnored(matcher.Ignores)
}

// selectFunction resolves --function-id, prompting when it is absent.
func selectFunction(command *cobra.Command, local *config.Local, id string) (config.Function, error) {
	if id != "" {
		return local.Function(id)
	}

	functions, err := local.Functions()
	if err != nil {
		return config.Function{}, err
	}
	if len(functions) == 0 {
		return config.Function{}, fmt.Errorf(
			"no functions found in %s", filepath.Base(local.Path()))
	}
	if len(functions) == 1 {
		return functions[0], nil
	}

	// Ports questionsRunFunctions. Without a terminal this reports
	// --function-id rather than blocking, which is what the prompt package's
	// NonInteractive does with the Flag below.
	options := make([]prompt.Option, 0, len(functions))
	for _, function := range functions {
		options = append(options, prompt.Option{
			Label: function.Name + " (" + function.ID + ")",
			Value: function.ID,
		})
	}

	selected, err := prompt.New(app.Flags().Force).Choice(prompt.Choice{
		Message: "Which function would you like to run?",
		Options: options,
		Flag:    "--function-id",
		Filter:  true,
	})
	if err != nil {
		return config.Function{}, err
	}

	return local.Function(selected)
}

// resolvePort validates an explicit port or finds a free one.
func resolvePort(requested int) (int, error) {
	if requested > 0 {
		if !docker.PortAvailable(requested) {
			return 0, fmt.Errorf("port %d is already in use by another process", requested)
		}

		return requested, nil
	}

	port, found := docker.FindPort(portSearchStart, portSearchEnd)
	if !found {
		return 0, fmt.Errorf(
			"could not find an available port. " +
				"Please select a port with 'appwrite run --port YOUR_PORT' command")
	}

	return port, nil
}

// printSettings shows what the local run will use, and what it will ignore.
func printSettings(command *cobra.Command, function config.Function) {
	out := command.OutOrStdout()

	command.Printf("  runtime:    %s\n", function.Runtime)
	command.Printf("  entrypoint: %s\n", function.Entrypoint)
	command.Printf("  path:       %s\n", function.Path)
	command.Printf("  commands:   %s\n", function.Commands)
	command.Printf("  scopes:     %s\n", strings.Join(function.Scopes, ", "))
	command.Println()

	output.Log(out, "If you wish to change your local settings, update the "+
		"appwrite.config.json file and rerun the 'appwrite run' command.")
	output.Log(out, "Permissions, events, CRON and timeouts don't apply when running locally.")
}

// collectVariables assembles the environment the container receives.
//
// Precedence is remote settings first, then .env, then the APPWRITE_FUNCTION_*
// values -- so a local .env overrides production, and the injected values
// cannot be shadowed by either.
func collectVariables(
	command *cobra.Command,
	local *config.Local,
	function config.Function,
	options runOptions,
) ([]string, map[string]string) {
	out := command.OutOrStdout()

	keys := []string{}
	variables := map[string]string{}
	set := func(key, value string) {
		if _, seen := variables[key]; !seen {
			keys = append(keys, key)
		}
		variables[key] = value
	}

	// The project client is shared by the two things here that talk to the API,
	// and neither is fatal: a run that cannot reach the API still starts the
	// function, it just starts it without production variables or credentials.
	api, apiErr := runProjectAPI(local)

	if options.WithVariables {
		switch {
		case apiErr != nil:
			warnNoVariables(out, apiErr)
		default:
			variables, err := listFunctionVariables(api, function.ID)
			if err != nil {
				warnNoVariables(out, err)
			} else {
				for _, variable := range variables {
					set(variable.GetString("key"), variable.GetString("value"))
				}
			}
		}
	}

	envPath := filepath.Join(local.ResolveResourcePath("functions", function.Path), ".env")
	if contents, err := os.ReadFile(envPath); err == nil {
		envKeys, envValues := dotenv.ParseOrdered(string(contents))
		for _, key := range envKeys {
			set(key, envValues[key])
		}
	}

	userVariableCount := len(keys)

	set("APPWRITE_FUNCTION_API_ENDPOINT", localEndpoint(local))
	set("APPWRITE_FUNCTION_ID", function.ID)
	set("APPWRITE_FUNCTION_NAME", function.Name)
	// Empty in the TypeScript too -- there is no deployment when running
	// locally, and the runtime expects the key to exist.
	set("APPWRITE_FUNCTION_DEPLOYMENT", "")
	set("APPWRITE_FUNCTION_PROJECT_ID", projectID(local))
	set("APPWRITE_FUNCTION_RUNTIME_NAME", docker.RuntimeNames[function.RuntimeName()])
	set("APPWRITE_FUNCTION_RUNTIME_VERSION", function.Runtime)

	// The credentials the function authenticates with. A failure here is a
	// warning, not an error: the TypeScript runs the function anyway, and one
	// that never calls the API does not need them.
	credentials := runCredentials{}
	if apiErr != nil {
		output.Warn(out, "Dynamic API key not generated. Header x-appwrite-key "+
			"will not be set. Reason: %s", apiErr)
	} else {
		minted, err := mintRunCredentials(api, options.UserID, function.Scopes)
		if err != nil {
			output.Warn(out, "Dynamic API key not generated. Header "+
				"x-appwrite-key will not be set. Reason: %s", err)
		}
		credentials = minted
	}

	headers := map[string]string{
		"x-appwrite-key":      credentials.FunctionKey,
		"x-appwrite-trigger":  "http",
		"x-appwrite-event":    "",
		"x-appwrite-user-id":  options.UserID,
		"x-appwrite-user-jwt": credentials.UserJWT,
	}
	if encoded, err := json.Marshal(headers); err == nil {
		set("OPEN_RUNTIMES_HEADERS", string(encoded))
	}

	if userVariableCount > 0 {
		printMaskedVariables(command, keys[:userVariableCount], variables)
	}

	return keys, variables
}

// printMaskedVariables shows which variables were loaded without their values.
//
// The TypeScript prints a fixed run of asterisks capped at 16, so the length of
// a short secret is not leaked either.
func printMaskedVariables(command *cobra.Command, keys []string, variables map[string]string) {
	for _, key := range keys {
		length := min(len(variables[key]), 16)
		command.Printf("  %s: %s\n", key, strings.Repeat("*", length))
	}
	command.Println()
}

// localEndpoint is the endpoint injected into the function.
func localEndpoint(local *config.Local) string {
	if value := local.Data.GetString("endpoint"); value != "" {
		return value
	}

	global, err := preferences()
	if err != nil {
		return ""
	}

	return global.CurrentValue("endpoint")
}

func projectID(local *config.Local) string {
	return local.Data.GetString("projectId")
}
