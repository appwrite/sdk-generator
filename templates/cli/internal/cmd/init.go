//go:build !browser

package cmd

import (
	"encoding/json"
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// `init bucket|team|topic|collection|table` only prompt and write
// appwrite.config.json -- nothing here touches the API, which is why they land
// before `init project|function|site|skill`. Those four create resources
// remotely or download templates and arrive with the SDK wiring.

// yesNo is the two-option list the TypeScript uses for every boolean, compared
// case-insensitively against "yes". Kept as a list rather than a confirm so the
// rendered question matches.
var yesNo = prompt.Options("No", "Yes")

func newInitCommand() *cobra.Command {
	command := &cobra.Command{
		Use:   "init",
		Short: "Init a new Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			return command.Help()
		},
	}

	command.AddCommand(
		newInitProjectCommand(),
		newInitFunctionCommand(),
		newInitSiteCommand(),
		newInitSkillCommand(),
		newInitBucketCommand(),
		newInitTeamCommand(),
		newInitTopicCommand(),
		newInitCollectionCommand(),
		newInitTableCommand(),
	)

	return command
}

// initContext is what every local `init` subcommand needs.
type initContext struct {
	local    *config.Local
	prompter prompt.Prompter
}

func newInitContext() (*initContext, error) {
	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		return nil, err
	}

	return &initContext{local: local, prompter: prompt.New(app.Flags().Force)}, nil
}

// truthy renders the yes/no list answer the way the TypeScript does.
func truthy(answer string) *bool {
	value := answer == "Yes"

	return &value
}

// finish writes the config and prints the two lines the TypeScript prints.
func (c *initContext) finish(command *cobra.Command, resource string) error {
	if err := c.local.Write(); err != nil {
		return err
	}

	out := command.OutOrStdout()
	output.Success(out, "Initialing %s", resource)
	output.Log(out, "Next you can use '%s push %s' to deploy the changes.",
		app.ExecutableName, resource)

	return nil
}

func newInitBucketCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "bucket",
		Aliases: []string{"buckets"},
		Short:   "Init a new Appwrite bucket",
		RunE: func(command *cobra.Command, args []string) error {
			context, err := newInitContext()
			if err != nil {
				return err
			}

			name, err := context.prompter.Text(prompt.Text{
				Message: "What would you like to name your bucket?",
				Default: "My Awesome Bucket",
			})
			if err != nil {
				return err
			}

			id, err := context.prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your bucket?",
				Default: appwrite.UniqueSentinel,
			})
			if err != nil {
				return err
			}

			fileSecurity, err := context.prompter.Choice(prompt.Choice{
				Message: "Enable File-Security configuring permissions for individual file",
				Options: yesNo,
				Default: "No",
			})
			if err != nil {
				return err
			}

			enabled := true
			if err := context.local.AddBucket(config.Bucket{
				ID:           appwrite.ResolveID(id),
				Name:         name,
				FileSecurity: truthy(fileSecurity),
				Enabled:      &enabled,
			}); err != nil {
				return err
			}

			return context.finish(command, "bucket")
		},
	}
}

func newInitTeamCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "team",
		Aliases: []string{"teams"},
		Short:   "Init a new Appwrite team",
		RunE: func(command *cobra.Command, args []string) error {
			context, err := newInitContext()
			if err != nil {
				return err
			}

			name, err := context.prompter.Text(prompt.Text{
				Message: "What would you like to name your team?",
				Default: "My Awesome Team",
			})
			if err != nil {
				return err
			}

			id, err := context.prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your team?",
				Default: appwrite.UniqueSentinel,
			})
			if err != nil {
				return err
			}

			if err := context.local.AddTeam(config.Team{
				ID:   appwrite.ResolveID(id),
				Name: name,
			}); err != nil {
				return err
			}

			return context.finish(command, "team")
		},
	}
}

func newInitTopicCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "topic",
		Aliases: []string{"topics"},
		Short:   "Init a new Appwrite topic",
		RunE: func(command *cobra.Command, args []string) error {
			context, err := newInitContext()
			if err != nil {
				return err
			}

			name, err := context.prompter.Text(prompt.Text{
				Message: "What would you like to name your messaging topic?",
				Default: "My Awesome Topic",
			})
			if err != nil {
				return err
			}

			id, err := context.prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your messaging topic?",
				Default: appwrite.UniqueSentinel,
			})
			if err != nil {
				return err
			}

			if err := context.local.AddTopic(config.Topic{
				ID:   appwrite.ResolveID(id),
				Name: name,
			}); err != nil {
				return err
			}

			return context.finish(command, "topic")
		},
	}
}

// databaseChoice is the shared first half of `init collection` and `init table`.
//
// Both ask the same four questions about the database before asking about the
// resource itself, and both create the database entry when it does not exist.
// The only difference is which config array holds it.
type databaseChoice struct {
	ID   string
	Name string
	// New reports whether the database still has to be added to the config.
	New bool
}

// chooseDatabase asks whether to reuse a configured database or declare one.
//
// The "New or Existing" question is only asked when there is something to
// reuse; with an empty config the TypeScript skips it and takes the New branch,
// which is what the empty `existing` list reproduces here.
func (c *initContext) chooseDatabase(resource, key string) (databaseChoice, error) {
	configured, err := c.databases(key)
	if err != nil {
		return databaseChoice{}, err
	}

	method := "New"
	if len(configured) > 0 {
		method, err = c.prompter.Choice(prompt.Choice{
			Message: fmt.Sprintf("What database would you like to use for your %s", resource),
			Options: prompt.Options("New", "Existing"),
			Default: "New",
		})
		if err != nil {
			return databaseChoice{}, err
		}
	}

	if method == "Existing" {
		options := make([]prompt.Option, 0, len(configured))
		for _, database := range configured {
			options = append(options, prompt.Option{
				Label: database.Name + " (" + database.ID + ")",
				Value: database.ID,
			})
		}

		id, err := c.prompter.Choice(prompt.Choice{
			Message: fmt.Sprintf("Choose the %s database", resource),
			Options: options,
			Filter:  true,
		})
		if err != nil {
			return databaseChoice{}, err
		}

		for _, database := range configured {
			if database.ID == id {
				return databaseChoice{ID: database.ID, Name: database.Name}, nil
			}
		}

		return databaseChoice{}, fmt.Errorf("database '%s' is not in the configuration", id)
	}

	name, err := c.prompter.Text(prompt.Text{
		Message: "What would you like to name your database?",
		Default: "My Awesome Database",
	})
	if err != nil {
		return databaseChoice{}, err
	}

	id, err := c.prompter.Text(prompt.Text{
		Message: "What ID would you like to have for your database?",
		Default: appwrite.UniqueSentinel,
	})
	if err != nil {
		return databaseChoice{}, err
	}

	return databaseChoice{ID: appwrite.ResolveID(id), Name: name, New: true}, nil
}

// databases reads one of the two database arrays out of the config.
func (c *initContext) databases(key string) ([]config.Database, error) {
	value, ok := c.local.Data.Get(key)
	if !ok {
		return nil, nil
	}

	encoded, err := config.Marshal(value)
	if err != nil {
		return nil, err
	}

	var databases []config.Database
	if err := json.Unmarshal(encoded, &databases); err != nil {
		return nil, err
	}

	return databases, nil
}

func newInitCollectionCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "collection",
		Aliases: []string{"collections"},
		Short:   "Init a new Appwrite collection",
		RunE: func(command *cobra.Command, args []string) error {
			context, err := newInitContext()
			if err != nil {
				return err
			}

			database, err := context.chooseDatabase("collection", "databases")
			if err != nil {
				return err
			}

			name, err := context.prompter.Text(prompt.Text{
				Message: "What would you like to name your collection?",
				Default: "My Awesome Collection",
			})
			if err != nil {
				return err
			}

			id, err := context.prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your collection?",
				Default: appwrite.UniqueSentinel,
			})
			if err != nil {
				return err
			}

			documentSecurity, err := context.prompter.Choice(prompt.Choice{
				Message: "Enable document security for configuring permissions for individual documents",
				Options: yesNo,
				Default: "No",
			})
			if err != nil {
				return err
			}

			enabled := true
			if database.New {
				if err := context.local.AddDatabase(config.Database{
					ID: database.ID, Name: database.Name, Enabled: &enabled,
				}); err != nil {
					return err
				}
			}

			// Empty slices rather than nil: the TypeScript writes
			// `"attributes": []`, and a missing key would read as "not yet
			// pulled" rather than "declared with none".
			if err := context.local.AddCollection(config.Collection{
				ID:               appwrite.ResolveID(id),
				DatabaseID:       database.ID,
				Name:             name,
				DocumentSecurity: truthy(documentSecurity),
				Enabled:          &enabled,
				Attributes:       []any{},
				Indexes:          []any{},
			}); err != nil {
				return err
			}

			return context.finish(command, "collection")
		},
	}
}

func newInitTableCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "table",
		Aliases: []string{"tables"},
		Short:   "Init a new Appwrite table",
		RunE: func(command *cobra.Command, args []string) error {
			context, err := newInitContext()
			if err != nil {
				return err
			}

			database, err := context.chooseDatabase("table", "tablesDB")
			if err != nil {
				return err
			}

			name, err := context.prompter.Text(prompt.Text{
				Message: "What would you like to name your table?",
				Default: "My Awesome Table",
			})
			if err != nil {
				return err
			}

			id, err := context.prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your table?",
				Default: appwrite.UniqueSentinel,
			})
			if err != nil {
				return err
			}

			rowSecurity, err := context.prompter.Choice(prompt.Choice{
				Message: "Enable Row-Security configuring permissions for individual rows",
				Options: yesNo,
				Default: "No",
			})
			if err != nil {
				return err
			}

			enabled := true
			if database.New {
				if err := context.local.AddTablesDB(config.Database{
					ID: database.ID, Name: database.Name, Enabled: &enabled,
				}); err != nil {
					return err
				}
			}

			if err := context.local.AddTable(config.Table{
				ID:          appwrite.ResolveID(id),
				DatabaseID:  database.ID,
				Name:        name,
				RowSecurity: truthy(rowSecurity),
				Enabled:     &enabled,
				Permissions: []string{},
				Columns:     []any{},
				Indexes:     []any{},
			}); err != nil {
				return err
			}

			return context.finish(command, "table")
		},
	}
}
