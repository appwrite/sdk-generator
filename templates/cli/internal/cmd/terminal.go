//go:build !browser

package cmd

import (
	"errors"
	"fmt"
	"log/slog"
	"os"
	"os/signal"
	"runtime"
	"syscall"

	"github.com/spf13/cobra"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	terminalclient "github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// envAPIKey is read by the terminal alone. The other commands take a key from
// `appwrite client --key`, which the terminal honours too; the variable lets a
// key be used for one session without storing it.
const envAPIKey = "APPWRITE_API_KEY"

// newTerminalCommand opens the terminal interface on a project, or on the
// organizations of the login when no project is named or configured.
func newTerminalCommand() *cobra.Command {
	var projectID, theme string
	var debug bool

	command := &cobra.Command{
		Use:   "terminal",
		Short: "Browse your projects in an interactive terminal interface",
		Long: "Browse the databases, functions, sites, storage, users, teams and messaging of a project, " +
			"with its usage, in an interactive terminal interface.\n\n" +
			"It opens on the project in --project-id, APPWRITE_PROJECT_ID or the nearest " +
			config.LocalFileName + ", and on the organizations of your login otherwise. " +
			"It signs in as your login, or with an API key from APPWRITE_API_KEY.",
		Example: "  appwrite terminal\n" +
			"  appwrite terminal --project-id shop\n" +
			"  APPWRITE_API_KEY=standard_… appwrite terminal --project-id shop",
		Args: cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			switch theme {
			case "auto", "dark", "light":
			default:
				return fmt.Errorf("unknown theme %q, use auto, dark or light", styles.Clean(theme))
			}

			slog.SetDefault(slog.New(slog.DiscardHandler))
			if debug {
				f, err := os.OpenFile("debug.log", os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0o600)
				if err != nil {
					return fmt.Errorf("opening debug.log: %w", err)
				}
				defer func() { _ = f.Close() }()
				slog.SetDefault(slog.New(slog.NewTextHandler(f, &slog.HandlerOptions{Level: slog.LevelDebug})))
			}

			ctx, err := sdk.Load(app.ExecutableName, app.Version)
			if err != nil {
				return err
			}
			opts := terminalOptions(ctx.Global, projectID)
			if err := opts.Check(); err != nil {
				return errors.New(styles.Clean(err.Error()))
			}

			signals, stop := signal.NotifyContext(command.Context(), os.Interrupt, syscall.SIGTERM)
			defer stop()
			agent := fmt.Sprintf("AppwriteCLI/%s (%s; %s) terminal", app.Version, runtime.GOOS, runtime.GOARCH)
			c, info, err := terminalclient.Connect(signals, opts, agent)
			if err != nil {
				if signals.Err() != nil {
					return signals.Err()
				}
				msg := "could not connect: " + styles.Clean(err.Error())
				if _, ok := errors.AsType[*appwrite.Error](err); !ok {
					msg += "\nis the instance up? check the endpoint of your login with `appwrite client --debug`"
				}
				return errors.New(msg)
			}

			if err := tui.Run(signals, c, info, theme); err != nil {
				return errors.New(styles.Clean(err.Error()))
			}

			return nil
		},
	}

	flags := command.Flags()
	flags.StringVar(&projectID, "project-id", "", "Project `ID` to open, instead of APPWRITE_PROJECT_ID or the project config's")
	flags.StringVar(&theme, "theme", "auto", "Color `theme`: auto follows the terminal, dark or light also paint the background")
	flags.BoolVar(&debug, "debug", false, "Write debug logs to debug.log in the working directory")

	return command
}

// terminalOptions resolves the project and endpoint as a project command does:
// the flag, then the environment, then the nearest project config. The
// endpoint is checked against the login when it connects.
func terminalOptions(global *config.Global, projectID string) terminalclient.Options {
	opts := terminalclient.Options{
		Project:    projectID,
		Key:        os.Getenv(envAPIKey),
		Endpoint:   os.Getenv(sdk.EnvEndpoint),
		Global:     global,
		SDKVersion: app.Version,
	}
	if opts.Project == "" {
		opts.Project = os.Getenv(sdk.EnvProjectID)
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil || local == nil {
		return opts
	}
	if opts.Project == "" {
		opts.Project = local.Data.GetString("projectId")
	}
	if opts.Endpoint == "" {
		opts.Endpoint = local.Data.GetString("endpoint")
	}

	return opts
}
