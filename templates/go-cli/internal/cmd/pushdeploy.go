package cmd

import (
	"fmt"
	"io"
	"net/url"
	"os"
	"path/filepath"
	"slices"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/deploy"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/dotenv"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports pushFunction (templates/cli/lib/commands/push.ts:3603), pushSite
// (:3441) and pushSettings (:3358), together with the Push methods they call --
// Push.pushFunctions (:1726), Push.pushSites (:2238) and Push.pushSettings
// (:1303).
//
// Two deliberate divergences, both named once here rather than at each site.
//
// The TypeScript pushes every selected resource concurrently, each with its own
// spinner row. This pushes them one at a time behind a single row, which keeps
// the request sequence deterministic -- which is what makes a recorded trace of a
// push worth comparing at all -- and keeps interleaved build logs attributable. The settings writes are sequential for a different and
// stronger reason -- see applyEnabled.
//
// The TypeScript reads build logs over a realtime WebSocket and falls back to
// polling when it cannot -- in the recorded trace `GET /realtime` answers 400
// and it polls anyway. Only the polling half is ported. Build logs ARE
// streamed, from the same poll that decides the deployment's outcome, so the
// visible difference is latency: a line appears within one pollDebounce rather
// than the moment it is written. That also drops the TypeScript's `l` keypress
// toggle, which exists to pause a firehose the poll cannot produce.

const (
	// pollDebounce is POLL_DEBOUNCE (push.ts:117), the gap between deployment
	// status reads.
	pollDebounce = 2 * time.Second

	// deploymentTimeout is DEPLOYMENT_TIMEOUT_MS. It measures time without
	// PROGRESS, not total build time, so a slow but advancing build is never
	// cut off.
	deploymentTimeout        = 10 * time.Minute
	deploymentTimeoutMinutes = 10

	// waitingJoke replaces the status line once a deployment has sat in
	// `waiting` for long enough to look stuck.
	waitingJokeThreshold = 30 * time.Second
	waitingJokeURL       = "https://xkcd.com/303/"

	// screenshotFinalizationTimeout is
	// SITE_SCREENSHOT_FINALIZATION_TIMEOUT_MS: how long a ready site is given
	// to produce its preview screenshots before it is reported without them.
	screenshotFinalizationTimeout = 30 * time.Second

	// deploymentFileField is the multipart field the archive is sent under.
	deploymentFileField = "code"
)

// settingsPolicies maps a config `auth.security` key to its policy route and
// the body it takes.
//
// The routes are kebab-case, like the ids pull reads. `total` policies write
// null for "no limit", where the config spells zero the same way; `enabled`
// policies are plain booleans.
var settingsPolicies = []struct {
	Key   string
	Route string
	Field string
}{
	{"duration", policySessionDuration, "duration"},
	{"limit", policyUserLimit, "total"},
	{"sessionsLimit", policySessionLimit, "total"},
	{"passwordDictionary", policyPasswordDictionary, "enabled"},
	{"passwordHistory", policyPasswordHistory, "total"},
	{"personalDataCheck", policyPasswordPersonal, "enabled"},
	{"sessionAlerts", policySessionAlert, "enabled"},
}

func newPushSettingsCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "settings",
		Short: "Push project name, services and auth settings",
		RunE: func(command *cobra.Command, args []string) error {
			return runPushSettings(command)
		},
	}
}

// runPushSettings ports pushSettings (push.ts:3358) and Push.pushSettings
// (:1303).
//
// The read half mirrors `pull settings` exactly -- same three requests, same
// reshaping -- because the change table compares what pull would have written
// against what the config says.
func runPushSettings(command *cobra.Command) error {
	out := command.OutOrStdout()

	context, err := newPushContext()
	if err != nil {
		return err
	}

	console, _, err := consoleClient()
	if err != nil {
		return err
	}

	projectID := context.local.Data.GetString("projectId")

	organizationID, err := resolveOrganizationID(
		out, console, context.local.Data.GetString("organizationId"), projectID)
	if err != nil {
		return err
	}
	scoped := console.Clone().WithoutResponseFormat().SetOrganization(organizationID)

	project := jsonx.NewObject()
	err = scoped.Call("GET", pathProjects+"/"+url.PathEscape(projectID), nil, project)
	if err != nil {
		return err
	}

	policies, mockPhones := jsonx.NewObject(), jsonx.NewObject()
	if err := context.api.Call("GET", "/project/policies", nil, policies); err != nil {
		return err
	}
	if err := context.api.Call("GET", "/project/mock-phones", nil, mockPhones); err != nil {
		return err
	}

	remote := buildSettings(project, policies, mockPhones)
	local := context.local.Data.GetObject("settings")
	if local == nil {
		local = jsonx.NewObject()
	}

	output.Log(out, "Checking for changes ...")

	var changes []change
	changes = append(changes, settingsChanges(remote, local, "services", "Service")...)
	changes = append(changes, settingsChanges(
		remote.GetObject("auth"), local.GetObject("auth"), "methods", "Auth method")...)
	changes = append(changes, settingsChanges(
		remote.GetObject("auth"), local.GetObject("auth"), "security", "Auth security")...)

	if len(changes) > 0 {
		// Reuses the resource change table. Its headers read `id` and `key`
		// where the TypeScript's read `group` and `setting`; the values are the
		// same and human output is not contractual.
		printChanges(command, changes)

		approved, err := context.prompter.Confirm(prompt.Question{
			Message: "Would you like to apply these changes?",
			Default: true,
			Flag:    "--force",
		})
		if err != nil {
			return err
		}
		if !approved {
			output.Warn(out, "Skipping push action. Changes were not applied.")
			output.Success(out, "Successfully pushed 0 project settings.")

			return nil
		}
	}

	output.Log(out, "Pushing project settings ...")

	if name := context.local.Data.GetString("projectName"); name != "" {
		body := jsonx.NewObject()
		body.Set("name", name)

		err := scoped.Call("PATCH", pathProjects+"/"+url.PathEscape(projectID), body, nil)
		if err != nil {
			return err
		}
	}

	if err := context.applySettings(command, local); err != nil {
		return err
	}

	output.Success(out, "Successfully pushed all project settings.")

	return nil
}

// applySettings writes the config's settings block to the project.
func (c *pushContext) applySettings(command *cobra.Command, settings *jsonx.Object) error {
	out := command.OutOrStdout()

	if services := settings.GetObject("services"); services != nil {
		output.Log(out, "Applying service statuses ...")
		if err := timed(out, len(services.Keys()), "service statuses", func() error {
			return c.applyEnabled("/project/services/", services)
		}); err != nil {
			return err
		}
	}

	if protocols := settings.GetObject("protocols"); protocols != nil {
		output.Log(out, "Applying protocol statuses ...")
		if err := timed(out, len(protocols.Keys()), "protocol statuses", func() error {
			return c.applyEnabled("/project/protocols/", protocols)
		}); err != nil {
			return err
		}
	}

	auth := settings.GetObject("auth")
	if auth == nil {
		return nil
	}

	if security := auth.GetObject("security"); security != nil {
		output.Log(out, "Applying auth security settings ...")
		if err := timed(out, len(security.Keys()), "auth security settings", func() error {
			return c.applySecurity(security)
		}); err != nil {
			return err
		}
	}

	if methods := auth.GetObject("methods"); methods != nil {
		output.Log(out, "Applying auth methods statuses ...")
		if err := timed(out, len(methods.Keys()), "auth method statuses", func() error {
			return c.applyEnabled("/project/auth-methods/", methods)
		}); err != nil {
			return err
		}
	}

	return nil
}

// timed reports what a settings step did and how long it took.
//
// A settings push is four steps of one line each and can take the better part
// of a minute, which leaves no way to tell a slow step from a hung one. The
// count and the duration turn "it is stuck" into "the auth methods took
// forty seconds", which is the question anyone actually has.
func timed(out io.Writer, count int, what string, run func() error) error {
	started := time.Now()
	if err := run(); err != nil {
		return err
	}

	output.Log(out, "Applied %d %s in %s",
		count, what, time.Since(started).Round(time.Millisecond))

	return nil
}

// applyEnabled writes an `{id: enabled}` object, one PATCH per entry.
//
// ONE AT A TIME, and deliberately so -- this is a divergence from the
// TypeScript's Promise.all (push.ts:1332), which has the same defect.
//
// These look like independent routes and are not. Every one of them
// read-modify-writes a nested field of the SAME `projects` row
// (Project/Services/Update.php:76). Sending them together is unsafe: each
// handler builds its new `services` array from the snapshot it read, and
// although updateDocument re-reads the row under `SELECT ... FOR UPDATE`, the
// supplied nested array REPLACES the freshly read one. Two concurrent writes
// then lose one of the two changes.
//
// It is also not faster. The row lock serialises them server-side regardless,
// measured at ~1.25s per waiter across all four batches, so concurrency buys
// contention and a lost-update window in exchange for nothing.
//
// The real fix is a bulk settings endpoint that merges every change under one
// transaction; until that exists, the honest client behaviour is to queue.
func (c *pushContext) applyEnabled(base string, states *jsonx.Object) error {
	for _, key := range states.Keys() {
		value, _ := states.Get(key)

		body := jsonx.NewObject()
		body.Set("enabled", value)

		if err := c.api.Call("PATCH", base+url.PathEscape(key), body, nil); err != nil {
			return err
		}
	}

	return nil
}

// applySecurity writes the auth.security block.
//
// Each key is its own policy route, and a key the config omits is left alone --
// absent means "no opinion", not "reset it".
func (c *pushContext) applySecurity(security *jsonx.Object) error {
	// One at a time, for the reason given on applyEnabled: these write the same
	// project row. The TypeScript collects them into securityUpdates and awaits
	// them together (push.ts:1355), which has the same lost-update window.
	for _, policy := range settingsPolicies {
		value, ok := security.Get(policy.Key)
		if !ok {
			continue
		}

		body := jsonx.NewObject()
		if policy.Field == "total" {
			// nullablePolicyTotal (push.ts:754): zero and null both mean "no
			// limit", and the API takes null.
			body.Set(policy.Field, nullablePolicyTotal(security, policy.Key))
		} else {
			body.Set(policy.Field, value)
		}

		if err := c.api.Call("PATCH", "/project/policies/"+policy.Route, body, nil); err != nil {
			return err
		}
	}

	if _, ok := security.Get("mockNumbers"); ok {
		return c.applyMockNumbers(security)
	}

	return nil
}

// nullablePolicyTotal renders a policy total, with zero as null.
func nullablePolicyTotal(security *jsonx.Object, key string) any {
	value, _ := security.Get(key)
	if value == nil {
		return nil
	}
	if security.GetInt64(key) == 0 {
		return nil
	}

	return value
}

// applyMockNumbers reconciles the project's mock phone numbers.
//
// The remote is walked first so a number the config dropped is deleted, one
// whose OTP changed is updated, and only what is left is created. Replacing
// them wholesale would delete and recreate every unchanged number.
func (c *pushContext) applyMockNumbers(security *jsonx.Object) error {
	desired, order := desiredMockNumbers(security)

	remote, _, err := client.PaginateInto(func(queries []string) (*jsonx.Object, error) {
		listing := jsonx.NewObject()
		err := c.api.Call("GET",
			"/project/mock-phones?"+client.EncodeQueries(queries), nil, listing)
		if err != nil {
			return nil, err
		}

		return listing, nil
	}, "mockNumbers", nil, client.DefaultPageSize)
	if err != nil {
		return err
	}

	for _, entry := range remote {
		number := entry.GetString("number")

		otp, wanted := desired[number]
		if !wanted {
			err := c.api.Call("DELETE",
				"/project/mock-phones/"+url.PathEscape(number), nil, nil)
			if err != nil {
				return err
			}

			continue
		}

		if entry.GetString("otp") != otp {
			body := jsonx.NewObject()
			body.Set("otp", otp)

			err := c.api.Call("PUT",
				"/project/mock-phones/"+url.PathEscape(number), body, nil)
			if err != nil {
				return err
			}
		}

		delete(desired, number)
	}

	for _, number := range order {
		otp, remaining := desired[number]
		if !remaining {
			continue
		}

		body := jsonx.NewObject()
		body.Set("number", number)
		body.Set("otp", otp)

		if err := c.api.Call("POST", "/project/mock-phones", body, nil); err != nil {
			return err
		}
	}

	return nil
}

// desiredMockNumbers reads the config's mock numbers, keeping their order.
func desiredMockNumbers(security *jsonx.Object) (map[string]string, []string) {
	desired := map[string]string{}
	var order []string

	value, _ := security.Get("mockNumbers")
	items, _ := value.([]any)
	for _, item := range items {
		entry, ok := item.(*jsonx.Object)
		if !ok {
			continue
		}

		phone := entry.GetString("phone")
		if phone == "" {
			continue
		}
		if _, seen := desired[phone]; !seen {
			order = append(order, phone)
		}
		desired[phone] = entry.GetString("otp")
	}

	return desired, order
}

// settingsChanges compares one nested settings group.
//
// Ports getObjectChanges (change-approval.ts:63). It iterates the REMOTE keys,
// so a setting the config invents but the project does not have is not a
// change -- there is nothing to overwrite.
func settingsChanges(remote, local *jsonx.Object, group, label string) []change {
	if remote == nil {
		return nil
	}

	remoteGroup := remote.GetObject(group)
	if remoteGroup == nil {
		return nil
	}

	var localGroup *jsonx.Object
	if local != nil {
		localGroup = local.GetObject(group)
	}

	var changes []change
	for _, key := range remoteGroup.Keys() {
		remoteValue, _ := remoteGroup.Get(key)

		var localValue any
		if localGroup != nil {
			localValue, _ = localGroup.Get(key)
		}

		if equalValues(remoteValue, localValue) {
			continue
		}

		changes = append(changes, change{
			ID:     label,
			Key:    key,
			Remote: renderValue(remoteValue),
			Local:  renderValue(localValue),
		})
	}

	return changes
}

// deployable describes one resource type that carries code.
//
// Functions and sites differ in their fields and their console URLs and in
// nothing else, so the push runs once over this description rather than twice
// over two near-identical copies.
type deployable struct {
	resourceIdentity
	// IDFlag is the flag naming one resource, `--function-id`.
	IDFlag string
	// IDField is the body field the id is created under, `functionId`.
	IDField string
	// RuleResourceType is how the proxy service names the resource.
	RuleResourceType string
	// MismatchKey is the field that cannot be changed after creation. A remote
	// that disagrees is reported rather than overwritten.
	MismatchKey string
	// WriteKeys are the fields create and update send, in the order the
	// TypeScript builds them. Only the ones the config actually has are sent;
	// the TypeScript passes the rest as undefined, which JSON.stringify drops.
	WriteKeys []string
	// ApproveKeys is the config schema, which is what the change table
	// compares. Ports KeysFunction / KeysSite (config.ts:69).
	ApproveKeys []string
	// DeploymentKeys are the config fields sent alongside the archive.
	DeploymentKeys []string
	// OmitWhenEmpty are fields left out of a request when the config leaves
	// them blank, rather than sent as "".
	//
	// An entrypoint is the case: it is required on create and optional on
	// update, so a blank one in the config means "unchanged". Sending "" would
	// clear the value already on the server. Deliberately NOT every field --
	// an empty `schedule` really does mean "unschedule it".
	OmitWhenEmpty []string
	// ConsoleURL renders the deployment's console page.
	ConsoleURL func(base, slug, resourceID, deploymentID string) string
}

var deployables = []deployable{
	{
		resourceIdentity: functionIdentity,
		IDFlag:           "function-id", IDField: "functionId",
		RuleResourceType: "function", MismatchKey: "runtime",
		WriteKeys: []string{
			"name", "runtime", "execute", "events", "schedule", "timeout",
			"enabled", "logging", "entrypoint", "commands", "scopes",
			"buildSpecification", "runtimeSpecification", "deploymentRetention",
		},
		ApproveKeys: []string{
			"path", "$id", "execute", "name", "enabled", "logging", "runtime",
			"buildSpecification", "runtimeSpecification", "deploymentRetention",
			"scopes", "events", "schedule", "timeout", "entrypoint", "commands",
			"vars", "ignore",
		},
		DeploymentKeys: []string{"entrypoint", "commands"},
		OmitWhenEmpty:  []string{"entrypoint"},
		ConsoleURL: func(base, slug, resourceID, deploymentID string) string {
			return fmt.Sprintf("%s/console/%s/functions/function-%s/deployment-%s",
				base, slug, resourceID, deploymentID)
		},
	},
	{
		resourceIdentity: siteIdentity,
		IDFlag:           "site-id", IDField: "siteId",
		RuleResourceType: "site", MismatchKey: "framework",
		WriteKeys: []string{
			"name", "framework", "logging", "timeout", "installCommand",
			"buildCommand", "outputDirectory", "buildRuntime", "adapter",
			"startCommand", "fallbackFile", "installationId",
			"providerRepositoryId", "providerBranch", "providerSilentMode",
			"providerRootDirectory", "buildSpecification",
			"runtimeSpecification", "deploymentRetention",
		},
		ApproveKeys: []string{
			"path", "$id", "name", "logging", "timeout", "framework",
			"buildRuntime", "adapter", "installCommand", "buildCommand",
			"outputDirectory", "fallbackFile", "installationId",
			"providerRepositoryId", "providerBranch", "providerSilentMode",
			"providerRootDirectory", "buildSpecification",
			"runtimeSpecification", "deploymentRetention", "startCommand",
			"vars",
		},
		DeploymentKeys: []string{"installCommand", "buildCommand", "outputDirectory"},
		ConsoleURL: func(base, slug, resourceID, deploymentID string) string {
			return fmt.Sprintf("%s/console/%s/sites/site-%s/deployments/deployment-%s",
				base, slug, resourceID, deploymentID)
		},
	},
}

// deployOptions are the flags one push run was given.
type deployOptions struct {
	// ResourceID limits the push to one resource.
	ResourceID string
	// Async skips waiting for the deployment to build.
	Async bool
	// Code pushes the source; --no-code updates the resource only.
	Code bool
	// Activate makes the new deployment live once it is ready.
	Activate bool
	// ActivateSet reports whether --activate was given, which is what decides
	// whether the question is asked.
	ActivateSet bool
	// WithVariables replaces the resource's variables from its .env file.
	WithVariables bool
	// Logs streams the deployment's build log while it builds; --no-logs
	// reports status transitions only.
	Logs bool
}

func newPushDeployableCommand(resource deployable) *cobra.Command {
	options := deployOptions{Code: true, Logs: true}
	var activate string

	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Push " + resource.Label + " in the current directory.",
		Args:    everyResourceArgument(),
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error {
			if command.Flags().Changed("force") {
				app.Flags().Force = true
			}

			options.ActivateSet = command.Flags().Changed("activate")
			if options.ActivateSet {
				parsed, err := parseFlagBool(activate)
				if err != nil {
					return err
				}
				options.Activate = parsed
			}

			return runPushDeployable(command, resource, options)
		},
	}

	flags := command.Flags()
	flags.StringVarP(&options.ResourceID, resource.IDFlag, "f", "",
		"ID of "+resource.Singular+" to run")
	// `-f` is the global --force shorthand, and the TypeScript rebinds it to
	// --function-id here; commander resolves that in favour of the local
	// option, cobra panics on the duplicate shorthand. Declaring `force`
	// locally stops cobra merging the persistent flag at all, so the shorthand
	// belongs to this command and --force still works spelled out.
	flags.Bool("force", false, "Skip confirmation prompts.")
	flags.BoolVarP(&options.Async, "async", "A", false,
		"Don't wait for "+resource.Label+" deployments status")
	negatableBool(flags, &options.Code, "code",
		"Push the "+resource.Singular+"'s code")
	negatableBool(flags, &options.Logs, "logs",
		"Stream deployment build logs")
	flags.StringVar(&activate, "activate", "",
		"Activate the "+resource.Singular+"'s deployment after it is ready.")
	flags.Lookup("activate").NoOptDefVal = "true"
	flags.BoolVar(&options.WithVariables, "with-variables", false,
		"Push "+resource.Singular+" variables.")

	return command
}

// parseFlagBool reads the value of `--activate [value]`.
//
// Ports parseBool(). A bare `--activate` is true; anything else has to say so.
func parseFlagBool(value string) (bool, error) {
	switch strings.ToLower(strings.TrimSpace(value)) {
	case "", "true", "1", "yes", "y":
		return true, nil
	case "false", "0", "no", "n":
		return false, nil
	}

	return false, fmt.Errorf("invalid boolean value %q", value)
}

func runPushDeployable(
	command *cobra.Command,
	resource deployable,
	options deployOptions,
) error {
	out := command.OutOrStdout()

	context, err := newPushContext()
	if err != nil {
		return err
	}

	entries, err := context.selectDeployables(resource, options.ResourceID)
	if err != nil {
		return err
	}
	if len(entries) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		// Log, not Hint, unlike its three siblings. The TypeScript prints this
		// one as an ordinary line and the CLIs are compared on their output.
		output.Log(out, "%s", resource.syncHint())

		return nil
	}

	// Validation runs BEFORE anything is sent, so a missing entrypoint is one
	// question at the start rather than a failure halfway through.
	output.Log(out, "Validating %s ...", resource.Label)
	entries, err = context.completeDeployables(out, resource, entries)
	if err != nil {
		return err
	}

	// Everything was skipped for missing a required field, so there is nothing
	// left to push. Without this the push carried on with an empty list and
	// still asked whether to create a deployment.
	if len(entries) == 0 {
		output.Log(out, "No %s left to push.", resource.Label)

		return nil
	}

	approved, err := context.approveChanges(command, approvalRequest{
		Resources: entries,
		Fetch: func(local *jsonx.Object) (*jsonx.Object, error) {
			return context.fetchResource(resource, local.GetString("$id"))
		},
		Keys:     resource.ApproveKeys,
		SkipKeys: []string{"vars"},
		Plural:   resource.Label,
	})
	if err != nil {
		return err
	}
	if !approved {
		return nil
	}

	pushCode := options.Code
	if pushCode {
		confirmed, err := context.prompter.Confirm(prompt.Question{
			Message: "Do you want to create a deployment for your " + resource.Label + "?",
			Default: true,
			Flag:    "--force",
		})
		if err != nil {
			return err
		}
		pushCode = confirmed
	}

	activate := true
	if pushCode && !options.ActivateSet {
		// --force answers this, where the TypeScript asks it even under
		// --force. A deliberate divergence: --force in this CLI means "do not
		// ask", and a confirmation that survives it hangs a CI run.
		confirmed, err := context.prompter.Confirm(prompt.Question{
			Message: "Do you want to activate the deployment after it is ready?",
			Default: true,
			Flag:    "--activate",
		})
		if err != nil {
			return err
		}
		activate = confirmed
	} else if options.ActivateSet {
		activate = options.Activate
	}

	output.Log(out, "Pushing %s ...", resource.Label)

	started := time.Now()
	summary := pushSummary{}

	for _, entry := range entries {
		context.pushDeployable(command, resource, entry, deployRun{
			Async:         options.Async,
			Code:          pushCode,
			Activate:      activate,
			WithVariables: options.WithVariables,
			// `logs && !asyncDeploy` (push.ts:1751): an async push returns
			// before the build starts, so there is no log to stream.
			Logs: options.Logs && !options.Async,
			// The label is only worth the width when two logs could interleave.
			LabelLogs: len(entries) > 1,
		}, &summary)
	}

	summary.report(command, resource, options.Async, time.Since(started))

	return nil
}

// deployRun is one resource's resolved push settings.
type deployRun struct {
	Async         bool
	Code          bool
	Activate      bool
	WithVariables bool
	Logs          bool
	LabelLogs     bool
}

// failedDeployment names a deployment that did not become ready.
type failedDeployment struct {
	Name       string
	Reason     string
	ConsoleURL string
}

// pushSummary accumulates what a push run achieved.
type pushSummary struct {
	Pushed   int
	Deployed int
	Failed   []failedDeployment
}

// report prints the closing lines of a push.
func (s *pushSummary) report(
	command *cobra.Command,
	resource deployable,
	async bool,
	elapsed time.Duration,
) {
	out := command.OutOrStdout()

	// The link and nothing else: the spinner row above has already named the
	// resource and the reason.
	for _, failure := range s.Failed {
		output.Log(out, "Deployment page: %s", failure.ConsoleURL)
	}

	if async {
		output.Success(out, "Successfully pushed %d %s.", s.Pushed, resource.Label)

		return
	}

	seconds := fmt.Sprintf("%.1fs", elapsed.Seconds())

	switch {
	case s.Pushed == 0:
		pushTally{Pushed: 0, Failed: len(s.Failed)}.report(out, resource.Label)
	case s.Deployed == 0:
		output.Failure(out, "Deployed none of the %d %s pushed, in %s.",
			s.Pushed, plural(s.Pushed, resource), seconds)
	case s.Deployed != s.Pushed:
		output.Warn(out, "Deployed %d of %d %s in %s. %d failed.",
			s.Deployed, s.Pushed, plural(s.Pushed, resource), seconds,
			s.Pushed-s.Deployed)
	case s.Pushed == 1:
		output.Success(out, "Successfully deployed 1 %s in %s.", resource.Singular, seconds)
	default:
		output.Success(out, "Successfully deployed %d %s in %s.",
			s.Pushed, resource.Label, seconds)
	}
}

// plural names a resource by count.
func plural(count int, resource deployable) string {
	if count == 1 {
		return resource.Singular
	}

	return resource.Label
}

// selectDeployables resolves which configured resources to push.
func (c *pushContext) selectDeployables(
	resource deployable,
	resourceID string,
) ([]*jsonx.Object, error) {
	entries := c.local.ResourceEntries(resource.ConfigKey)

	if resourceID != "" {
		for _, entry := range entries {
			if entry.GetString("$id") == resourceID {
				return []*jsonx.Object{entry}, nil
			}
		}

		return nil, fmt.Errorf("%s '%s' not found", resource.Singular, resourceID)
	}

	if len(entries) == 0 {
		return nil, nil
	}

	return c.selectResources(resource.Label, resource.Singular, entries)
}

// completeDeployables asks for the fields a push cannot proceed without.
//
// The answer is written back to the config, so the question is asked once per
// resource rather than once per push.
func (c *pushContext) completeDeployables(
	out io.Writer,
	resource deployable,
	entries []*jsonx.Object,
) ([]*jsonx.Object, error) {
	changed := false
	usable := make([]*jsonx.Object, 0, len(entries))

	for _, entry := range entries {
		field, message, label := "", "", ""

		switch {
		case resource.Name == "function" && entry.GetString("entrypoint") == "":
			field, message, label = "entrypoint", "Enter the entrypoint", "an entrypoint"
		case resource.Name == "site" && entry.GetString("buildCommand") == "" &&
			siteRequiresBuildCommand(entry):
			field, message, label = "buildCommand", "Enter the build command", "a build command"
		default:
			usable = append(usable, entry)

			continue
		}

		name := entry.GetString("name")
		if name == "" {
			name = entry.GetString("$id")
		}

		// Required on CREATE only. The API asks for none of these -- `functions
		// create` requires function-id, name and runtime, and nothing else --
		// so an update that happens to leave the field blank locally is not an
		// error, it just has nothing to say about it. The value already on the
		// server is kept, which writeBody arranges by omitting the key.
		if c.resourceExists(resource, entry.GetString("$id")) {
			output.Log(out, "%s %s has no %s set locally. Keeping the one on the server.",
				capitalizeFirst(resource.Singular), name, field)
			usable = append(usable, entry)

			continue
		}

		// Which one, and what is missing. The TypeScript logs this before it
		// prompts (push.ts:3656); without it the question arrives as a bare
		// "Enter the entrypoint" with no clue which of ten functions it is for.
		output.Log(out, "%s %s is missing %s.",
			capitalizeFirst(resource.Singular), name, label)

		// --all says "every resource, do not ask me". It is also how a pipeline
		// pushes, so stopping on a question there is the opposite of what was
		// asked -- and the answer is data the CLI cannot invent. The resource is
		// reported and skipped; the rest of the push proceeds.
		if app.Flags().All {
			output.Log(out,
				"Skipping it: set %q in %s, or push it without --all to be asked.",
				field, config.LocalFileName)

			continue
		}

		answer, err := c.prompter.Text(prompt.Text{
			Message:  message,
			Flag:     "--force",
			Validate: prompt.Required(field),
		})
		if err != nil {
			return nil, err
		}

		entry.Set(field, answer)
		c.local.UpsertByID(resource.ConfigKey, entry)
		changed = true
		usable = append(usable, entry)
	}

	if !changed {
		return usable, nil
	}

	return usable, c.local.Write()
}

// resourceExists reports whether the resource is already on the server.
//
// Only asked for a resource whose config leaves a create-required field blank,
// so it costs one extra request in the one case that needs the answer.
//
// A 404 means "not there", so the field is required. Any OTHER failure --
// offline, an expired session, a 500 -- leaves the question unanswered, and the
// safe answer is the stricter one: treat it as a create and ask.
func (c *pushContext) resourceExists(resource deployable, id string) bool {
	// No id to ask about, or nothing to ask: both mean the answer is unknown,
	// and unknown takes the strict branch.
	if id == "" || c.api == nil {
		return false
	}

	if _, err := c.fetchResource(resource, id); err != nil {
		return false
	}

	return true
}

// capitalizeFirst starts a sentence with the resource name, which is stored
// lowercase for use mid-sentence.
func capitalizeFirst(value string) string {
	if value == "" {
		return value
	}

	return strings.ToUpper(value[:1]) + value[1:]
}

// siteRequiresBuildCommand reports whether a site has to be built.
//
// Ports siteRequiresBuildCommand (utils.ts:142). A static site on the `other`
// framework is served as it stands, so it is the one that needs no command.
func siteRequiresBuildCommand(site *jsonx.Object) bool {
	return !(site.GetString("framework") == "other" && site.GetString("adapter") == "static")
}

// fetchResource reads one resource from the API.
func (c *pushContext) fetchResource(resource deployable, id string) (*jsonx.Object, error) {
	remote := jsonx.NewObject()
	err := c.api.Call("GET", resource.Path+"/"+url.PathEscape(id), nil, remote)
	if err != nil {
		return nil, err
	}

	return remote, nil
}

// pushDeployable pushes one resource and, unless asked not to, waits for it.
//
// Errors are reported and recorded rather than returned: one broken function
// must not abandon the others, which is what the TypeScript's per-resource
// try/catch achieves.
func (c *pushContext) pushDeployable(
	command *cobra.Command,
	resource deployable,
	entry *jsonx.Object,
	run deployRun,
	summary *pushSummary,
) {
	out := command.OutOrStdout()
	id := entry.GetString("$id")
	name := entry.GetString("name")

	output.Log(out, "Pushing %s %s ( %s ) ...", resource.Singular, name, id)

	exists := true
	remote, err := c.fetchResource(resource, id)
	switch {
	case err != nil && isNotFound(err):
		exists = false
	case err != nil:
		output.Failure(out, "Failed to push %s %s: %s", resource.Singular, name, err)

		return
	}

	if exists {
		local := entry.GetString(resource.MismatchKey)
		if remoteValue := remote.GetString(resource.MismatchKey); remoteValue != local {
			output.Failure(out,
				"%s mismatch! (local=%s,remote=%s) Please delete remote %s or update your %s",
				strings.ToUpper(resource.MismatchKey[:1])+resource.MismatchKey[1:],
				local, remoteValue, resource.Singular, config.LocalFileName)

			return
		}

		err = c.api.Call("PUT", resource.Path+"/"+url.PathEscape(id),
			writeBody(entry, resource.WriteKeys, resource.OmitWhenEmpty, "", ""), nil)
	} else {
		err = c.api.Call("POST", resource.Path,
			writeBody(entry, resource.WriteKeys, resource.OmitWhenEmpty,
				resource.IDField, id), nil)
	}
	if err != nil {
		output.Failure(out, "Failed to push %s %s: %s", resource.Singular, name, err)

		return
	}

	// Ensured on every push, not only on create: the TypeScript calls it in
	// both branches, and a function whose rule was deleted in the console gets
	// it back.
	if err := c.ensureDefaultRule(command, resource, id); err != nil {
		output.Failure(out, "Failed to push %s %s: %s", resource.Singular, name, err)

		return
	}

	if run.WithVariables {
		if err := c.replaceVariables(resource, entry); err != nil {
			output.Failure(out, "Failed to push %s %s: %s", resource.Singular, name, err)

			return
		}
	}

	if !run.Code {
		summary.Pushed++
		summary.Deployed++

		return
	}

	deployment, err := c.createDeployment(command, resource, entry, run.Activate)
	if err != nil {
		output.Failure(out, "Failed to push %s %s: %s", resource.Singular, name, err)

		return
	}
	summary.Pushed++

	if run.Async {
		return
	}

	c.awaitDeployment(command, resource, entry, deployment, run, summary)
}

// writeBody builds a create or update body from the config entry.
//
// Only keys the config actually carries are sent. The TypeScript passes the
// rest as undefined and JSON.stringify drops them, so sending an explicit null
// would be a behaviour change -- it would clear the field on the remote.
func writeBody(
	entry *jsonx.Object,
	keys []string,
	omitWhenEmpty []string,
	idField, id string,
) *jsonx.Object {
	body := jsonx.NewObject()
	if idField != "" {
		body.Set(idField, id)
	}

	for _, key := range keys {
		value, ok := entry.Get(key)
		if !ok {
			continue
		}
		if text, isText := value.(string); isText && text == "" &&
			slices.Contains(omitWhenEmpty, key) {
			continue
		}

		body.Set(key, value)
	}

	return body
}

// replaceVariables rewrites a resource's variables from its .env file.
//
// Every remote variable is deleted first: the .env is the source of truth, and
// merging would keep one the user removed.
func (c *pushContext) replaceVariables(resource deployable, entry *jsonx.Object) error {
	base := resource.Path + "/" + url.PathEscape(entry.GetString("$id")) + "/variables"

	listing := jsonx.NewObject()
	if err := c.api.Call("GET", base, nil, listing); err != nil {
		return err
	}

	value, _ := listing.Get("variables")
	items, _ := value.([]any)
	for _, item := range items {
		variable, ok := item.(*jsonx.Object)
		if !ok {
			continue
		}
		err := c.api.Call("DELETE",
			base+"/"+url.PathEscape(variable.GetString("$id")), nil, nil)
		if err != nil {
			return err
		}
	}

	path := c.local.ResolveResourcePath(resource.ConfigKey, entry.GetString("path"))
	contents, err := os.ReadFile(filepath.Join(path, ".env"))
	if err != nil {
		// No .env is not an error: the TypeScript swallows the read and pushes
		// an empty set, which is how variables are cleared.
		return nil
	}

	names, values := dotenv.ParseOrdered(string(contents))
	for _, name := range names {
		body := jsonx.NewObject()
		body.Set("variableId", appwrite.Unique())
		body.Set("key", name)
		body.Set("value", values[name])
		body.Set("secret", false)

		if err := c.api.Call("POST", base, body, nil); err != nil {
			return err
		}
	}

	return nil
}

// createDeployment packages the resource and uploads it.
func (c *pushContext) createDeployment(
	command *cobra.Command,
	resource deployable,
	entry *jsonx.Object,
	activate bool,
) (*jsonx.Object, error) {
	out := command.OutOrStdout()

	configured := entry.GetString("path")
	if configured == "" {
		return nil, fmt.Errorf("no path configured for %s", resource.Singular)
	}

	path := c.local.ResolveResourcePath(resource.ConfigKey, configured)
	entries, err := os.ReadDir(path)
	if err != nil || len(entries) == 0 {
		return nil, fmt.Errorf("path not found or empty: %s", configured)
	}

	fields := make([]client.FormField, 0, len(resource.DeploymentKeys)+1)
	for _, key := range resource.DeploymentKeys {
		value, ok := entry.Get(key)
		if !ok {
			continue
		}
		// Same rule as writeBody: a blank entrypoint means "use the one the
		// function already has", not "deploy with none".
		if text, isText := value.(string); isText && text == "" &&
			slices.Contains(resource.OmitWhenEmpty, key) {
			continue
		}

		fields = append(fields, client.FormField{
			Name:  key,
			Value: fmt.Sprint(scalarOf(value)),
		})
	}
	fields = append(fields, client.FormField{
		Name: "activate", Value: fmt.Sprint(activate),
	})

	result, err := deploy.Push(deploy.Options{
		ResourcePath: path,
		// The resource's own rules ADD to .gitignore here, unlike the
		// emulation path in internal/docker, which replaces it.
		ExtraIgnoreRules: normalizeIgnoreRules(entry),
		ProjectRoot:      c.local.Dirname(),
		Warn:             func(message string) { output.Warn(out, "%s", message) },
		CreateDeployment: func(packaged *deploy.Archive) (*jsonx.Object, error) {
			return deploy.Upload(c.api,
				resource.Path+"/"+url.PathEscape(entry.GetString("$id"))+"/deployments",
				fields, deploymentFileField, packaged)
		},
	})
	if err != nil {
		return nil, err
	}

	return result.Deployment, nil
}

// normalizeIgnoreRules reads a resource's `ignore` field as a pattern list.
//
// Ports normalizeIgnoreRules (push.ts:757). The schema declares a single
// string, but a config written by hand may carry an array, and both are
// accepted.
func normalizeIgnoreRules(entry *jsonx.Object) []string {
	value, ok := entry.Get("ignore")
	if !ok {
		return nil
	}

	if items, ok := value.([]any); ok {
		rules := make([]string, 0, len(items))
		for _, item := range items {
			if rule, ok := item.(string); ok && rule != "" {
				rules = append(rules, rule)
			}
		}

		return rules
	}

	text, ok := value.(string)
	if !ok || text == "" {
		return nil
	}

	var rules []string
	for _, line := range strings.Split(text, "\n") {
		if trimmed := strings.TrimSpace(line); trimmed != "" {
			rules = append(rules, trimmed)
		}
	}

	return rules
}

// awaitDeployment polls a deployment until it is ready, fails or stalls.
func (c *pushContext) awaitDeployment(
	command *cobra.Command,
	resource deployable,
	entry *jsonx.Object,
	deployment *jsonx.Object,
	run deployRun,
	summary *pushSummary,
) {
	out := command.OutOrStdout()
	id := entry.GetString("$id")
	name := entry.GetString("name")
	deploymentID := deployment.GetString("$id")
	consoleURL := c.deploymentConsoleURL(resource, id, deploymentID)

	tracker := newProgressTracker(deployment)

	// The status line, redrawn in place while the build runs. Off a terminal it
	// prints the same plain lines this did before there was a spinner.
	spinner := output.NewSpinner(out, output.SpinnerState{
		Status: "Deploying", Resource: name, ID: id,
		End: "Checking deployment status...",
	})

	// A discarded printer is cheaper than branching at every call site: with
	// --no-logs nothing is ever ingested, so nothing is ever printed. Logs go
	// through the spinner so each line lands above the status row.
	logPrinter := output.NewBuildLogPrinter(spinner.Log,
		resource.Name+":"+name, run.LabelLogs)
	ingest := func(deployment *jsonx.Object) {
		if run.Logs {
			logPrinter.Ingest(deployment.GetString("buildLogs"))
		}
	}

	var (
		waitingSince   time.Time
		readySince     time.Time
		activationDone bool
	)

	for {
		if tracker.stalled() {
			logPrinter.Complete()
			// getDeploymentTimeoutErrorMessage (push.ts:156). The summary
			// repeats it with the console link; this is the line in place.
			spinner.Fail("Error", fmt.Sprintf(
				"Deployment got stuck for more than %d minutes", deploymentTimeoutMinutes))
			summary.Failed = append(summary.Failed, failedDeployment{
				Name: name, Reason: "timeout", ConsoleURL: consoleURL,
			})

			return
		}

		var err error
		deployment, err = c.fetchDeployment(resource, id, deploymentID)
		if err != nil {
			logPrinter.Complete()
			spinner.Stop()
			output.Failure(out, "Failed to deploy %s %s: %s", resource.Singular, name, err)

			return
		}
		tracker.touch(deployment)
		ingest(deployment)

		status := deployment.GetString("status")
		if status == "waiting" {
			if waitingSince.IsZero() {
				waitingSince = time.Now()
			}
		} else {
			waitingSince = time.Time{}
		}

		switch status {
		case deploy.StatusReady:
			if run.Activate && !activationDone {
				body := jsonx.NewObject()
				body.Set("deploymentId", deploymentID)

				err := c.api.Call("PATCH",
					resource.Path+"/"+url.PathEscape(id)+"/deployment", body, nil)
				if err != nil {
					logPrinter.Complete()
					spinner.Stop()
					output.Failure(out, "Failed to activate %s %s: %s",
						resource.Singular, name, err)

					return
				}
				activationDone = true
			}

			// A ready site is given a moment to finish its preview
			// screenshots. The deployment is already live; this only decides
			// whether there is a picture to show for it.
			if resource.Name == "site" && !hasScreenshots(deployment) {
				if readySince.IsZero() {
					readySince = time.Now()
				}
				if time.Since(readySince) < screenshotFinalizationTimeout {
					// Named, not silent. The build has finished, so "Deploying"
					// is no longer true, and a row that stops changing for half
					// a minute after the last log line looks stuck.
					spinner.Update("Finalizing", "Finalizing deployment preview...")
					time.Sleep(pollDebounce)

					continue
				}
			}

			logPrinter.Complete()
			spinner.Succeed("Deployed", "")
			summary.Deployed++
			c.reportDeployment(command, resource, id, consoleURL, deployment)

			return

		case deploy.StatusFailed:
			logPrinter.Complete()
			spinner.Fail("Error", "Deployment failed")
			summary.Failed = append(summary.Failed, failedDeployment{
				Name: name, Reason: "failed", ConsoleURL: consoleURL,
			})

			return

		default:
			// Update is a no-op when nothing changed, which is most polls.
			spinner.Update("Deploying", progressText(status, waitingSince))
		}

		time.Sleep(pollDebounce)
	}
}

// fetchDeployment reads one deployment.
func (c *pushContext) fetchDeployment(
	resource deployable,
	id, deploymentID string,
) (*jsonx.Object, error) {
	deployment := jsonx.NewObject()
	err := c.api.Call("GET",
		resource.Path+"/"+url.PathEscape(id)+"/deployments/"+url.PathEscape(deploymentID),
		nil, deployment)
	if err != nil {
		return nil, err
	}

	return deployment, nil
}

// reportDeployment prints what a finished deployment produced.
//
// For a site that is a picture of the page as well as the links to it -- see
// pushpreview.go for why a screenshot is worth the bytes.
func (c *pushContext) reportDeployment(
	command *cobra.Command,
	resource deployable,
	id, consoleURL string,
	deployment *jsonx.Object,
) {
	out := command.OutOrStdout()

	if resource.Name == "site" {
		c.reportScreenshot(out, deployment)
	}

	if link := c.previewURL(resource, id); link != "" {
		output.Log(out, "Preview link: %s", link)
	}
	output.Log(out, "Deployment page: %s", consoleURL)
}

// hasScreenshots reports whether a site deployment has a preview image.
func hasScreenshots(deployment *jsonx.Object) bool {
	for _, key := range []string{"screenshotLight", "screenshotDark"} {
		if strings.TrimSpace(deployment.GetString(key)) != "" {
			return true
		}
	}

	return false
}

// progressText renders the status line for a deployment still building.
//
// Ports getDeploymentProgressText (push.ts:141), joke included: a deployment
// that has been `waiting` for half a minute is queued behind something, and
// saying so is more honest than repeating the status.
func progressText(status string, waitingSince time.Time) string {
	if status == "waiting" && !waitingSince.IsZero() &&
		time.Since(waitingSince) >= waitingJokeThreshold {
		return "Still waiting... " + waitingJokeURL
	}

	return "Status: " + status
}

// progressTracker decides when a deployment has stopped making progress.
//
// Ports createDeploymentTimeoutTracker (push.ts:184). The clock resets whenever
// the deployment CHANGES, so a ten-minute build that keeps logging is fine and
// a two-minute one that goes silent is not.
type progressTracker struct {
	signature  string
	lastChange time.Time
}

func newProgressTracker(deployment *jsonx.Object) *progressTracker {
	tracker := &progressTracker{lastChange: time.Now()}
	tracker.touch(deployment)

	return tracker
}

func (t *progressTracker) touch(deployment *jsonx.Object) {
	signature := progressSignature(deployment)
	if signature == t.signature {
		return
	}

	t.signature = signature
	t.lastChange = time.Now()
}

func (t *progressTracker) stalled() bool {
	return time.Since(t.lastChange) > deploymentTimeout
}

// progressSignature summarises the parts of a deployment that mean progress.
//
// Only the TAIL of the build log is compared, matching the TypeScript's
// 200-character slice: a log that keeps growing changes the length, and
// comparing megabytes of text on every poll would not.
func progressSignature(deployment *jsonx.Object) string {
	logs := deployment.GetString("buildLogs")
	tail := logs
	if len(tail) > 200 {
		tail = tail[len(tail)-200:]
	}

	return strings.Join([]string{
		deployment.GetString("status"),
		deployment.GetString("$updatedAt"),
		fmt.Sprint(len(logs)),
		tail,
		deployment.GetString("screenshotLight"),
		deployment.GetString("screenshotDark"),
	}, "\x00")
}

// ruleQueries selects a resource's manual proxy rule.
func ruleQueries(resourceType, id string) []string {
	return []string{
		`{"method":"limit","values":[1]}`,
		`{"method":"equal","attribute":"deploymentResourceType","values":["` + resourceType + `"]}`,
		`{"method":"equal","attribute":"deploymentResourceId","values":["` + id + `"]}`,
		`{"method":"equal","attribute":"trigger","values":["manual"]}`,
	}
}

// listRules reads the manual proxy rule for a resource.
func (c *pushContext) listRules(resource deployable, id string) (*jsonx.Object, error) {
	rules := jsonx.NewObject()
	err := c.api.Call("GET",
		"/proxy/rules?"+client.EncodeQueries(ruleQueries(resource.RuleResourceType, id)),
		nil, rules)
	if err != nil {
		return nil, err
	}

	return rules, nil
}

// previewURL is the resource's own domain, when it has exactly one.
func (c *pushContext) previewURL(resource deployable, id string) string {
	rules, err := c.listRules(resource, id)
	if err != nil || rules.GetInt64("total") != 1 {
		return ""
	}

	value, _ := rules.Get("rules")
	items, _ := value.([]any)
	if len(items) == 0 {
		return ""
	}
	rule, ok := items[0].(*jsonx.Object)
	if !ok {
		return ""
	}

	return "https://" + rule.GetString("domain")
}

// ensureDefaultRule gives a resource a default domain if it has none.
//
// Ports ensureDefaultFunctionRule (push.ts:896) and createDefaultSiteRule
// (:914). Without a console session the domain cannot be read, so the rule is
// skipped with a warning rather than failing the push -- an API key run in CI
// is expected to hit this.
func (c *pushContext) ensureDefaultRule(
	command *cobra.Command,
	resource deployable,
	id string,
) error {
	out := command.OutOrStdout()

	rules, err := c.listRules(resource, id)
	if err != nil {
		return err
	}
	value, _ := rules.Get("rules")
	if items, _ := value.([]any); len(items) > 0 {
		return nil
	}

	console, _, err := consoleClient()
	if err != nil {
		output.Warn(out,
			"Skipping default domain rule for %s: console session required to read "+
				"%s domains. Run `%s login` or create the domain in the Console.",
			id, resource.Singular, app.ExecutableName)

		return nil
	}

	variables := jsonx.NewObject()
	if err := console.Call("GET", "/console/variables", nil, variables); err != nil {
		return fmt.Errorf("read console variables: %w", err)
	}

	domains := c.ruleDomains(resource, variables)
	if len(domains) == 0 {
		return fmt.Errorf("_APP_DOMAIN_%s is not configured",
			strings.ToUpper(resource.Label))
	}

	body := jsonx.NewObject()
	body.Set("domain", appwrite.Unique()+"."+domains[0])
	body.Set(resource.IDField, id)

	output.Log(out, "Creating %s proxy rule for %s ...",
		resource.Singular, body.GetString("domain"))

	return c.api.Call("POST", "/proxy/rules/"+resource.Name, body, nil)
}

// ruleDomains reads the configured domains for a resource type.
//
// A function's domain is regionalised by replacing the first label, which is
// the TypeScript's stand-in until the API reports a regional functions domain
// of its own. Sites are not regionalised.
func (c *pushContext) ruleDomains(resource deployable, variables *jsonx.Object) []string {
	configured := variables.GetString("_APP_DOMAIN_" + strings.ToUpper(resource.Label))

	var domains []string
	for _, domain := range strings.Split(configured, ",") {
		domain = strings.TrimSpace(domain)
		if domain == "" {
			continue
		}
		if resource.Name == "function" {
			domain = c.regionalRuleDomain(domain)
		}
		domains = append(domains, domain)
	}

	return domains
}

// regionalRuleDomain swaps a Cloud domain's first label for the endpoint's
// region.
func (c *pushContext) regionalRuleDomain(domain string) string {
	region := endpointRegion(c.api.Endpoint)
	if region == "" {
		return domain
	}

	parts := strings.Split(domain, ".")
	if len(parts) < 3 {
		return domain
	}
	parts[0] = region

	return strings.Join(parts, ".")
}

// endpointRegion is the region label of a Cloud endpoint, empty when
// self-hosted or when the endpoint is the base host.
//
// Ports getCloudEndpointRegion().
func endpointRegion(endpoint string) string {
	base, ok := config.CloudBaseHost(endpoint)
	if !ok {
		return ""
	}

	parsed, err := url.Parse(endpoint)
	if err != nil {
		return ""
	}

	region, _, found := strings.Cut(parsed.Hostname(), ".")
	if !found || region == strings.Split(base, ".")[0] {
		return ""
	}

	return region
}

// deploymentConsoleURL builds the console page for a deployment.
func (c *pushContext) deploymentConsoleURL(
	resource deployable,
	id, deploymentID string,
) string {
	endpoint := c.api.Endpoint
	projectID := c.local.Data.GetString("projectId")

	return resource.ConsoleURL(
		consoleBaseURL(config.NormalizeCloudConsoleEndpoint(endpoint)),
		c.consoleProjectSlug(endpoint, projectID),
		id, deploymentID)
}

// consoleProjectSlug is the project segment of a console URL.
//
// Ports getConsoleProjectSlug (utils.ts:534). A self-hosted project needs its
// region, which is only knowable by asking the console -- hence a request in
// the middle of a push that otherwise touches nothing but the project.
func (c *pushContext) consoleProjectSlug(endpoint, projectID string) string {
	parsed, err := url.Parse(endpoint)
	if err != nil {
		return "project-" + projectID
	}

	if _, cloud := config.CloudBaseHost(endpoint); !cloud {
		region := c.projectRegion(projectID)
		if region == "" {
			region = "default"
		}

		return "project-" + region + "-" + projectID
	}

	if label, _, _ := strings.Cut(parsed.Hostname(), "."); len(label) == 3 {
		return "project-" + label + "-" + projectID
	}

	return "project-" + projectID
}

// projectRegion reads a self-hosted project's region from the console.
//
// A failure is not an error: the region only shapes a link, and a push that
// worked should not report a failure because a URL is less precise.
func (c *pushContext) projectRegion(projectID string) string {
	console, _, err := consoleClient()
	if err != nil {
		return ""
	}

	// Best effort, like the rest of this function: io.Discard because the
	// "resolved from project" notice belongs to the command the user ran, not
	// to a lookup done to decorate a URL.
	organizationID, err := resolveOrganizationID(
		io.Discard, console, c.local.Data.GetString("organizationId"), projectID)
	if err != nil {
		return ""
	}

	project := jsonx.NewObject()
	err = console.Clone().WithoutResponseFormat().
		SetOrganization(organizationID).
		Call("GET", pathProjects+"/"+url.PathEscape(projectID), nil, project)
	if err != nil {
		return ""
	}

	return project.GetString("region")
}
