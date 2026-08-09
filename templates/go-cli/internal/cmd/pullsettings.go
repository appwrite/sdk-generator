package cmd

import (
	"net/url"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/spf13/cobra"
)

// Ports pullSettings (templates/cli/lib/commands/pull.ts:271) and
// createSettingsObject (lib/utils.ts:32).
//
// Settings is the one pulled resource that is RESHAPED rather than filtered:
// the API returns `services`, `protocols` and `authMethods` as arrays of
// `{$id, enabled}`, and the config stores them as objects keyed by id. A
// service the API does not mention is left out entirely, not written as false
// -- absent means "the API has no opinion", and writing false would turn that
// into an instruction to disable it on the next push.

// settingsServices, settingsProtocols and settingsAuthMethods are the keys
// written, in the order createSettingsObject lists them. Order is part of the
// config file, so it is the TypeScript's rather than alphabetical.
var (
	settingsServices = []string{
		"account", "avatars", "databases", "locale", "health", "storage",
		"teams", "users", "sites", "functions", "graphql", "messaging",
	}
	settingsProtocols   = []string{"rest", "graphql", "websocket"}
	settingsAuthMethods = []string{
		"jwt", "phone", "invites", "anonymous",
		"email-otp", "magic-url", "email-password",
	}
)

// Policy identifiers as listPolicies returns them.
//
// Kebab-case, confirmed against a live project. The TypeScript reaches these
// through a ProjectPolicyId enum whose MEMBER names are lowercase run-together
// words -- Sessionduration, Passwordpersonaldata -- and transcribing those
// instead of the values yields ids that match nothing and a silently empty
// security block.
const (
	policySessionDuration    = "session-duration"
	policyUserLimit          = "user-limit"
	policySessionLimit       = "session-limit"
	policyPasswordHistory    = "password-history"
	policyPasswordDictionary = "password-dictionary"
	policyPasswordPersonal   = "password-personal-data"
	policySessionAlert       = "session-alert"
)

func newPullSettingsCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "settings",
		Short: "Pull your Appwrite project settings",
		RunE: func(command *cobra.Command, args []string) error {
			return runPullSettings(command)
		},
	}
}

func runPullSettings(command *cobra.Command) error {
	out := command.OutOrStdout()

	context, err := newProjectPull()
	if err != nil {
		return err
	}

	// The project itself is a CONSOLE resource read through its organization,
	// while the policies and mock numbers are project-scoped. Two clients, and
	// they are not interchangeable.
	api, _, err := consoleClient()
	if err != nil {
		return err
	}

	output.Log(out, "Pulling project settings ...")

	projectID := context.local.Data.GetString("projectId")

	organizationID, err := resolveOrganizationID(
		out, api, context.local.Data.GetString("organizationId"), projectID)
	if err != nil {
		return err
	}

	var project jsonx.Object
	// WithoutResponseFormat is required, not cosmetic: with the header the
	// console returns a legacy flat project and every array below is empty.
	err = api.Clone().WithoutResponseFormat().SetOrganization(organizationID).Call(
		"GET", pathProjects+"/"+url.PathEscape(projectID), nil, &project)
	if err != nil {
		return err
	}

	var policies, mockPhones jsonx.Object
	if err := context.api.Call("GET", "/project/policies", nil, &policies); err != nil {
		return err
	}
	if err := context.api.Call("GET", "/project/mock-phones", nil, &mockPhones); err != nil {
		return err
	}

	context.local.SetProject(projectID, project.GetString("name"))
	context.local.Data.Set("settings", buildSettings(&project, &policies, &mockPhones))

	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Successfully pulled all project settings.")

	return nil
}

// buildSettings reshapes a project response into the config's settings object.
func buildSettings(project, policies, mockPhones *jsonx.Object) *jsonx.Object {
	settings := jsonx.NewObject()

	settings.Set("services", enabledByID(project, "services", settingsServices))
	settings.Set("protocols", enabledByID(project, "protocols", settingsProtocols))

	auth := jsonx.NewObject()
	auth.Set("methods", enabledByID(project, "authMethods", settingsAuthMethods))
	auth.Set("security", buildSecurity(policies, mockPhones))
	settings.Set("auth", auth)

	return settings
}

// enabledByID turns an array of {$id, enabled} into an object keyed by id.
//
// Only the named keys are emitted, in the order given, and only when the API
// mentioned them.
func enabledByID(project *jsonx.Object, field string, keys []string) *jsonx.Object {
	states := map[string]any{}

	if value, ok := project.Get(field); ok {
		if items, ok := value.([]any); ok {
			for _, item := range items {
				entry, ok := item.(*jsonx.Object)
				if !ok {
					continue
				}
				if enabled, ok := entry.Get("enabled"); ok {
					states[entry.GetString("$id")] = enabled
				}
			}
		}
	}

	result := jsonx.NewObject()
	for _, key := range keys {
		if state, ok := states[key]; ok {
			result.Set(key, state)
		}
	}

	return result
}

// buildSecurity assembles the auth.security block from the policy list.
//
// A policy the API does not return is omitted. A policy whose total is 0 is
// written as null, not 0 -- zero means "no limit" to the API, and the config
// spells that as null.
func buildSecurity(policies, mockPhones *jsonx.Object) *jsonx.Object {
	byID := map[string]*jsonx.Object{}
	if value, ok := policies.Get("policies"); ok {
		if items, ok := value.([]any); ok {
			for _, item := range items {
				if entry, ok := item.(*jsonx.Object); ok {
					byID[entry.GetString("$id")] = entry
				}
			}
		}
	}

	security := jsonx.NewObject()

	if policy, ok := byID[policySessionDuration]; ok {
		if duration, ok := policy.Get("duration"); ok {
			security.Set("duration", duration)
		}
	}

	for _, mapping := range []struct{ key, policy string }{
		{"limit", policyUserLimit},
		{"sessionsLimit", policySessionLimit},
		{"passwordHistory", policyPasswordHistory},
	} {
		policy, ok := byID[mapping.policy]
		if !ok {
			continue
		}
		total, ok := policy.Get("total")
		if !ok {
			continue
		}
		if policy.GetInt64("total") == 0 {
			security.Set(mapping.key, nil)

			continue
		}
		security.Set(mapping.key, total)
	}

	for _, mapping := range []struct{ key, policy string }{
		{"passwordDictionary", policyPasswordDictionary},
		{"personalDataCheck", policyPasswordPersonal},
		{"sessionAlerts", policySessionAlert},
	} {
		if policy, ok := byID[mapping.policy]; ok {
			if enabled, ok := policy.Get("enabled"); ok {
				security.Set(mapping.key, enabled)
			}
		}
	}

	numbers := []any{}
	if value, ok := mockPhones.Get("mockNumbers"); ok {
		if items, ok := value.([]any); ok {
			for _, item := range items {
				entry, ok := item.(*jsonx.Object)
				if !ok {
					continue
				}
				mock := jsonx.NewObject()
				mock.Set("phone", entry.GetString("number"))
				mock.Set("otp", entry.GetString("otp"))
				numbers = append(numbers, mock)
			}
		}
	}
	security.Set("mockNumbers", numbers)

	return security
}
