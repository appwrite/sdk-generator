//go:build !browser

package cmd

import (
	"context"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/x509"
	"encoding/pem"
	"errors"
	"fmt"
	"io"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/expo"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/fastlane"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

const (
	apnsKeyPage      = "https://developer.apple.com/account/resources/authkeys/add"
	apnsKeysListPage = "https://developer.apple.com/account/resources/authkeys/list"
	appIDsPage       = "https://developer.apple.com/account/resources/identifiers/list"
	downloadTimeout  = 15 * time.Minute
	downloadPoll     = time.Second
)

var (
	errAutomaticKey = errors.New("automatic key creation chosen")
	apnsKeyFileName = regexp.MustCompile(`^AuthKey_([A-Z0-9]{10})\.p8$`)
	apnsKeyID       = regexp.MustCompile(`^[A-Z0-9]{10}$`)
)

type apnsOptions struct {
	keyPath     string
	keyID       string
	teamID      string
	bundleID    string
	createKey   bool
	reset       bool
	environment string
	setup       string
	// registerAppID registers the bundle ID as an App ID with Push
	// Notifications, without asking.
	registerAppID bool
}

type pushSetup struct {
	api       *client.Client
	prompter  prompt.Prompter
	out       io.Writer
	root      string
	keyDirs   []string
	downloads string
	open      func(string)
	ctx       context.Context
	getenv    func(string) string
	// apnsAdapters are the ways --create-key can create an APNs key, and
	// apnsSessions is where they keep a signed-in session.
	apnsAdapters *apns.Registry
	apnsSessions string
	// appleID is the Apple ID a key was just created with, so the App ID
	// check reuses that sign-in.
	appleID string
}

func newPushSetup(command *cobra.Command) (*pushSetup, error) {
	context, err := newPushContext()
	if err != nil {
		return nil, err
	}

	downloads := ""
	if home, err := os.UserHomeDir(); err == nil {
		downloads = filepath.Join(home, "Downloads")
	}

	var keyDirs []string
	for _, directory := range []string{context.local.Dirname(), "."} {
		if absolute, err := filepath.Abs(directory); err == nil {
			keyDirs = appendUnique(keyDirs, absolute)
		}
	}

	sessions := ""
	if prefs, err := config.GlobalPath(app.ExecutableName); err == nil {
		sessions = filepath.Join(filepath.Dir(prefs), "apns")
	}

	return &pushSetup{
		api:          context.api,
		prompter:     context.prompter,
		out:          command.OutOrStdout(),
		root:         ".",
		keyDirs:      keyDirs,
		downloads:    downloads,
		open:         openBrowser,
		ctx:          command.Context(),
		getenv:       os.Getenv,
		apnsAdapters: apnsAdapters(),
		apnsSessions: sessions,
	}, nil
}

func newInitApnsCommand() *cobra.Command {
	options := apnsOptions{}
	command := &cobra.Command{
		Use:   "apns",
		Short: "Set up Apple Push Notification service for Appwrite Push",
		RunE: func(command *cobra.Command, args []string) error {
			setup, err := newPushSetup(command)
			if err != nil {
				return err
			}
			detected, err := detectApple(setup.root)
			if err != nil {
				return err
			}

			return setup.apns(options, detected)
		},
	}

	flags := command.Flags()
	flags.StringVar(&options.keyPath, "key-path", "", "Path to an APNs auth key (.p8)")
	flags.StringVar(&options.keyID, "key-id", "", "APNs key ID")
	flags.StringVar(&options.teamID, "team-id", "", "Apple Developer team ID")
	flags.StringVar(&options.bundleID, "bundle-id", "", "iOS app bundle ID")
	flags.BoolVar(&options.createKey, "create-key", false, "Create the APNs key by signing in with your Apple ID (experimental)")
	flags.StringVar(&options.environment, "environment", string(apns.EnvironmentAll), "APNs environment to set up: production, sandbox or all")
	flags.StringVar(&options.setup, "provider", "", "How --create-key creates the key: appwrite, expo or fastlane. Defaults to $"+apnsSetupVariable+", then "+defaultApnsSetup)
	flags.BoolVar(&options.reset, "reset", false, "Sign in to Apple again instead of reusing the saved sign-in, and create the key (implies --create-key)")
	flags.BoolVar(&options.registerAppID, "register-app-id", false, "Register the bundle ID as an App ID with Push Notifications in the Apple Developer portal, signing in with your Apple ID (experimental)")
	command.MarkFlagsMutuallyExclusive("create-key", "key-path")
	command.MarkFlagsMutuallyExclusive("reset", "key-path")
	command.MarkFlagsMutuallyExclusive("reset", "key-id")
	command.MarkFlagsMutuallyExclusive("create-key", "key-id")

	return command
}

func (s *pushSetup) pick(value string, detected []string, question, flag string, validate func(string) error) (string, error) {
	if value != "" {
		if validate != nil {
			if err := validate(value); err != nil {
				return "", fmt.Errorf("%s: %w", flag, err)
			}
		}

		return value, nil
	}
	if len(detected) == 1 {
		return detected[0], nil
	}
	if len(detected) > 1 {
		return s.prompter.Choice(prompt.Choice{
			Message: question,
			Options: prompt.Options(detected...),
			Flag:    flag,
		})
	}

	return s.prompter.Text(prompt.Text{
		Message:  question,
		Flag:     flag,
		Validate: validate,
	})
}

func requiredValue(name string) func(string) error {
	return func(value string) error {
		if strings.TrimSpace(value) == "" {
			return fmt.Errorf("%s is required", name)
		}

		return nil
	}
}

func matchesPattern(pattern *regexp.Regexp, description string) func(string) error {
	return func(value string) error {
		if !pattern.MatchString(value) {
			return fmt.Errorf("%s", description)
		}

		return nil
	}
}

func (s *pushSetup) apns(options apnsOptions, detected appleApp) error {
	output.Log(s.out, "Setting up APNs ...")
	// --reset is for signing in again, which only creating a key does.
	if options.reset {
		options.createKey = true
	}

	environment, err := apnsEnvironment(options.environment)
	if err != nil {
		return err
	}
	// A setup chosen by name must exist before anything is written.
	if options.setup != "" || s.env(apnsSetupVariable) != "" {
		if _, err := s.apnsAdapter(options.setup); err != nil {
			return err
		}
	}
	wantProduction := environment != apns.EnvironmentSandbox
	wantSandbox := environment != apns.EnvironmentProduction

	bundleID, err := s.pick(options.bundleID, detected.BundleIDs,
		"Which iOS bundle ID should receive push notifications?", "--bundle-id", apns.CheckBundleID)
	if err != nil {
		return err
	}

	existing, err := s.providers("apns")
	if err != nil {
		return err
	}
	production := findProvider(existing, func(provider messagingProvider) bool {
		return provider.credential("bundleId") == bundleID && !provider.sandbox()
	})
	sandbox := findProvider(existing, func(provider messagingProvider) bool {
		return provider.credential("bundleId") == bundleID && provider.sandbox()
	})
	setUp := func(provider *messagingProvider, wanted bool) bool {
		return !wanted || (provider != nil && provider.Enabled)
	}
	if setUp(production, wantProduction) && setUp(sandbox, wantSandbox) && options.keyPath == "" && !options.reset && !app.Flags().Force {
		output.Success(s.out, "APNs%s is already set up for %s.", environmentLabel(environment), bundleID)
		output.Hint(s.out, "Pass --force or --key-path to replace the key.")
		s.configureXcode(detected, bundleID)
		s.ensureApplePlatform(bundleID)
		err := s.prepareAppID(provisionedTeam(production, sandbox, detected.teams(bundleID)), bundleID, options, false)
		s.apnsNextSteps()

		return err
	}

	teams := detected.teams(bundleID)
	for _, provider := range []*messagingProvider{production, sandbox} {
		if provider != nil && len(teams) == 0 && appleTeamID.MatchString(provider.credential("teamId")) {
			teams = []string{provider.credential("teamId")}
		}
	}
	pickTeam := func() (string, error) {
		return s.pick(options.teamID, teams,
			"What is your Apple Developer team ID?", "--team-id",
			matchesPattern(appleTeamID, "a team ID is 10 uppercase letters or digits"))
	}
	// Without a known team, signing in with the Apple ID chooses it, so the
	// question waits until the key has come from somewhere else.
	teamID := ""
	if options.teamID != "" || len(teams) > 0 || options.keyPath != "" {
		if teamID, err = pickTeam(); err != nil {
			return err
		}
	}

	keyPath := options.keyPath
	createdKeyID := ""
	if keyPath == "" {
		var keyTeam string
		keyPath, createdKeyID, keyTeam, err = s.apnsKey(teamID, options, environment)
		if err != nil {
			return err
		}
		if teamID == "" {
			teamID = keyTeam
		}
	}
	if teamID == "" {
		if teamID, err = pickTeam(); err != nil {
			return err
		}
	}

	key, err := readApnsKey(keyPath)
	if err != nil {
		return err
	}
	if options.keyPath != "" {
		s.checkKeyLocation(expandHome(keyPath))
	}

	keyID := options.keyID
	// A created key's ID comes from Apple; any other would not match the .p8.
	if createdKeyID != "" {
		if keyID != "" && keyID != createdKeyID {
			output.Warn(s.out, "Using the created key's ID %s instead of --key-id %s.", createdKeyID, keyID)
		}
		keyID = createdKeyID
	}
	if keyID == "" {
		if match := apnsKeyFileName.FindStringSubmatch(filepath.Base(keyPath)); match != nil {
			keyID = match[1]
		}
	}
	keyID, err = s.pick(keyID, nil, "What is the key ID of this APNs key?", "--key-id",
		matchesPattern(apnsKeyID, "a key ID is 10 uppercase letters or digits"))
	if err != nil {
		return err
	}

	for _, target := range []struct {
		sandbox  bool
		wanted   bool
		name     string
		existing *messagingProvider
	}{
		{false, wantProduction, "APNs (" + bundleID + ")", production},
		{true, wantSandbox, "APNs sandbox (" + bundleID + ")", sandbox},
	} {
		if !target.wanted {
			continue
		}
		body := map[string]any{
			"name":      target.name,
			"authKey":   key,
			"authKeyId": keyID,
			"teamId":    teamID,
			"bundleId":  bundleID,
			"sandbox":   target.sandbox,
			"enabled":   true,
		}
		if err := s.upsertProvider("apns", target.existing, body); err != nil {
			return err
		}
	}

	s.configureXcode(detected, bundleID)
	output.Success(s.out, "APNs%s is set up for %s.", environmentLabel(environment), bundleID)

	s.ensureApplePlatform(bundleID)
	// A key created a moment ago left a fresh sign-in to check the App ID with.
	err = s.prepareAppID(teamID, bundleID, options, createdKeyID != "")
	s.apnsNextSteps()

	return err
}

// ensureApplePlatform adds the bundle ID as an Apple platform of the project: without one, the
// API refuses the app's requests as an unknown origin, device registration included.
func (s *pushSetup) ensureApplePlatform(bundleID string) {
	manual := func() {
		output.Hint(s.out, "Add an Apple platform with the bundle ID %s to the project in the Appwrite console, or the app's requests are refused as an unknown origin.", bundleID)
	}
	var list struct {
		Platforms []struct {
			Type             string `json:"type"`
			Key              string `json:"key"`
			BundleIdentifier string `json:"bundleIdentifier"`
		} `json:"platforms"`
	}
	if err := s.api.Call("GET", "/project/platforms", nil, &list); err != nil {
		output.Warn(s.out, "Could not check the project's platforms: %s.", err)
		manual()

		return
	}
	for _, platform := range list.Platforms {
		if strings.HasPrefix(platform.Type, "apple") && (platform.BundleIdentifier == bundleID || platform.Key == bundleID) {
			return
		}
	}
	body := map[string]any{"platformId": "unique()", "name": "iOS (" + bundleID + ")", "bundleIdentifier": bundleID}
	if err := s.api.Call("POST", "/project/platforms/apple", body, nil); err != nil {
		output.Warn(s.out, "Could not add the Apple platform %s: %s.", bundleID, err)
		manual()

		return
	}
	output.Success(s.out, "Added the Apple platform %s to the project.", bundleID)
}

// apnsNextSteps says what else push on iOS needs when an Appwrite push provider carries the
// messages and APNs only wakes the app. APNs alone delivers the whole notification.
func (s *pushSetup) apnsNextSteps() {
	appwrite, err := s.providers("appwrite")
	if err != nil || findProvider(appwrite, func(provider messagingProvider) bool { return provider.Enabled }) == nil {
		return
	}
	output.Hint(s.out, "For messages while the app is in the background or swiped away, add a Notification Service Extension whose class extends AppwriteNotificationService, and put the app and the extension in one App Group, named by AppwritePushAppGroup in both Info.plists.")
}

// apnsKey finds or creates the APNs key and returns its path. With
// --create-key, signing in with the Apple ID is the only way, and a failure
// stops the command. Otherwise it is offered alongside the browser flow, and
// when it fails the other ways of providing the key remain.
func (s *pushSetup) apnsKey(teamID string, options apnsOptions, environment apns.Environment) (string, string, string, error) {
	create := options.createKey
	explicit := create
	automatic := ""
	if s.apnsAdapters != nil && len(s.apnsAdapters.Names()) > 0 {
		automatic = "Sign in with your Apple ID and create one (experimental)"
	}
	for {
		if !create {
			path, _, err := s.acquireKey("APNs auth key (.p8)", automatic, "Create one in the Apple Developer portal", "--key-path", apnsKeyPage,
				"In the Apple Developer portal, create a key with Apple Push Notifications service (APNs) enabled and download it.",
				func(path string) bool {
					_, err := readApnsKey(path)

					return strings.HasSuffix(path, ".p8") && err == nil
				})
			if !errors.Is(err, errAutomaticKey) {
				return path, "", "", err
			}
		}
		create = false

		path, keyID, keyTeam, err := s.createApnsKey(teamID, options, environment)
		var maxKeys *apns.MaxKeysError
		if errors.As(err, &maxKeys) {
			output.Warn(s.out, "Apple allows two APNs keys per team, and this team has no free slot. Keys on the team:")
			for _, key := range maxKeys.Keys {
				output.Log(s.out, "  %s (%s)", key.Name, key.ID)
			}
			output.Hint(s.out, "One key works for every app on the team. Reuse a .p8 you saved earlier, or revoke a key you no longer use at %s.", apnsKeysListPage)

			return "", "", "", errors.New("no APNs key can be created on this team. Pass --key-path with an existing .p8 instead")
		}
		if err == nil {
			return path, keyID, keyTeam, nil
		}
		// --create-key asked for a new key: falling back to a .p8 already in
		// the project folder would hide that none was created.
		if explicit {
			return "", "", "", fmt.Errorf("could not create the APNs key with your Apple ID: %w", err)
		}
		output.Warn(s.out, "Could not create the APNs key with your Apple ID: %s.", err)
		output.Log(s.out, "Provide the key another way instead.")
		automatic = ""
	}
}

const (
	// apnsSetupVariable picks the APNs key setup when --provider is not
	// passed, so a broken one can be swapped without a release.
	apnsSetupVariable = "APPWRITE_APNS_SETUP"
	defaultApnsSetup  = "appwrite"
	// The Apple ID and password, when set, answer the sign-in questions.
	appleIDVariable       = "APPWRITE_APPLE_ID"
	applePasswordVariable = "APPWRITE_APPLE_PASSWORD"
)

// e2eAppleHosts is set only in the CLI e2e build, which points Apple's sign-in
// and Developer portal at the mock API.
var e2eAppleHosts func() appwrite.Hosts

// apnsAdapters lists the APNs key setups this build offers. Each lives in its
// own package under internal/apns and is added here.
func apnsAdapters() *apns.Registry {
	adapter := appwrite.New()
	if e2eAppleHosts != nil {
		adapter.Hosts = e2eAppleHosts()
	}

	return apns.NewRegistry(adapter, expo.New(), fastlane.New())
}

// apnsAdapter picks the setup: --provider, then APPWRITE_APNS_SETUP, then
// appwrite.
func (s *pushSetup) apnsAdapter(setup string) (apns.Adapter, error) {
	if setup == "" {
		setup = s.env(apnsSetupVariable)
	}
	if setup == "" {
		setup = defaultApnsSetup
	}
	if s.apnsAdapters == nil {
		return nil, fmt.Errorf("APNs key setup %q is not available: this build has none", setup)
	}

	return s.apnsAdapters.Get(setup)
}

func (s *pushSetup) env(name string) string {
	if s.getenv == nil {
		return ""
	}

	return s.getenv(name)
}

func (s *pushSetup) context() context.Context {
	if s.ctx == nil {
		return context.Background()
	}

	return s.ctx
}

// apnsEnvironment reads --environment. Empty means both.
func apnsEnvironment(value string) (apns.Environment, error) {
	environment, err := apns.ParseEnvironment(value)
	if err != nil {
		return "", fmt.Errorf("--environment must be production, sandbox or all, not %q", value)
	}

	return environment, nil
}

// environmentLabel names a single environment in messages, and nothing for
// both.
func environmentLabel(environment apns.Environment) string {
	if environment == apns.EnvironmentAll {
		return ""
	}

	return " (" + string(environment) + ")"
}

// apnsAsker asks an APNs setup's questions through the CLI's prompts, so they
// look like every other question and fail with a clear message without a
// terminal.
type apnsAsker struct {
	prompter prompt.Prompter
}

func (a apnsAsker) Ask(question string, secret bool) (string, error) {
	return a.prompter.Text(prompt.Text{Message: question, Secret: secret, Validate: requiredValue("an answer")})
}

func (a apnsAsker) Choose(question string, options []string) (int, error) {
	choices := make([]prompt.Option, len(options))
	for index, label := range options {
		choices[index] = prompt.Option{Label: label, Value: strconv.Itoa(index)}
	}
	value, err := a.prompter.Choice(prompt.Choice{Message: question, Options: choices})
	if err != nil {
		return 0, err
	}

	return strconv.Atoi(value)
}

// appleRequest is how every Apple Developer portal step signs in: the Apple ID
// and password from the environment when set, the saved session unless reset.
func (s *pushSetup) appleRequest(adapter apns.Adapter, teamID string, reset bool) apns.Request {
	request := apns.Request{
		TeamID:   teamID,
		AppleID:  s.env(appleIDVariable),
		Password: s.env(applePasswordVariable),
		Reset:    reset,
		Asker:    apnsAsker{s.prompter},
		Log: func(format string, args ...any) {
			output.Log(s.out, format, args...)
		},
	}
	if s.apnsSessions != "" {
		request.SessionDir = filepath.Join(s.apnsSessions, adapter.Name())
	}

	return request
}

// logAppleSignIn says how the coming sign-in to Apple will happen.
func (s *pushSetup) logAppleSignIn(adapter apns.Adapter, request apns.Request) {
	output.Log(s.out, "Signing in to the Apple Developer portal (%s).", adapter.Name())
	if request.Reset {
		output.Log(s.out, "Signing in again: the saved Apple sign-in is not used (--reset).")
	}
	if request.AppleID != "" {
		output.Log(s.out, "Using the Apple ID from %s.", appleIDVariable)
	}
	if request.Password != "" {
		output.Log(s.out, "Using the Apple ID password from %s.", applePasswordVariable)
	}
}

// prepareAppID makes sure the bundle ID is an App ID with Push Notifications
// on the team: APNs refuses pushes for any other bundle ID (TopicDisallowed),
// and Xcode registers it only for a device build with the capability added.
//
// Every init apns runs it. It uses the sign-in a new key just left, or a
// saved one, and asks before each change; without either it asks whether to
// sign in, and skips when there is no terminal. --register-app-id signs in
// when needed, makes the changes without asking and fails the command when
// it cannot.
func (s *pushSetup) prepareAppID(teamID, bundleID string, options apnsOptions, signedIn bool) error {
	explicit := options.registerAppID
	fail := func(format string, args ...any) error {
		message := fmt.Sprintf(format, args...)
		if explicit {
			return errors.New(message)
		}
		output.Warn(s.out, "%s.", message)
		output.Hint(s.out, "APNs accepts pushes only for a bundle ID registered as an App ID with Push Notifications. Check it at %s.", appIDsPage)

		return nil
	}
	adapter, err := s.apnsAdapter(options.setup)
	if err != nil {
		return fail("Could not check the App ID for %s: %s", bundleID, err)
	}

	output.Log(s.out, "Checking the App ID for %s ...", bundleID)
	// A key created a moment ago left a fresh, saved sign-in: the check reuses
	// it for the same Apple ID, and --reset is not repeated.
	request := s.appleRequest(adapter, teamID, options.reset && !options.createKey)
	if signedIn && request.AppleID == "" {
		request.AppleID = s.appleID
	}
	request.SavedSignInOnly = signedIn || !explicit
	if !request.SavedSignInOnly {
		s.logAppleSignIn(adapter, request)
	}
	appID := apns.AppIDRequest{BundleID: bundleID, Ask: !explicit}
	result, err := apns.PrepareAppID(s.context(), adapter, request, appID)
	// Just signed in, or asked for explicitly: sign in again without asking.
	if errors.Is(err, apns.ErrSignInNeeded) && (signedIn || explicit) {
		request.SavedSignInOnly = false
		s.logAppleSignIn(adapter, request)
		result, err = apns.PrepareAppID(s.context(), adapter, request, appID)
	}
	if errors.Is(err, apns.ErrSignInNeeded) {
		answer, askErr := s.prompter.Choice(prompt.Choice{
			Message:     "Sign in with your Apple ID to check the App ID for " + bundleID + "?",
			Description: "APNs refuses pushes for a bundle ID that is not registered as an App ID with Push Notifications.",
			Options: []prompt.Option{
				{Label: "Sign in and check", Value: "check"},
				{Label: "Skip", Value: "skip"},
			},
		})
		if askErr != nil || answer != "check" {
			reason := ""
			if askErr != nil {
				reason = ": there is no saved Apple sign-in, and no terminal to sign in"
			}
			output.Hint(s.out, "Skipped the App ID check%s. Run `appwrite init apns --register-app-id` in a terminal, or check %s at %s.", reason, bundleID, appIDsPage)

			return nil
		}
		request.SavedSignInOnly = false
		s.logAppleSignIn(adapter, request)
		result, err = apns.PrepareAppID(s.context(), adapter, request, appID)
	}
	var unanswered *prompt.NonInteractiveError
	if errors.As(err, &unanswered) {
		return fail("Could not check the App ID for %s: %s", bundleID, needsTerminal(unanswered))
	}
	if err != nil {
		return fail("Could not check the App ID for %s: %s", bundleID, err)
	}

	switch {
	case result.Created:
		output.Success(s.out, "Registered %s as an App ID with Push Notifications on team %s.", bundleID, result.TeamID)
	case result.PushTurnedOn:
		output.Success(s.out, "Turned on Push Notifications for the App ID %s.", bundleID)
	case result.Registered && result.Push:
		output.Success(s.out, "%s is registered as an App ID with Push Notifications.", bundleID)
	case !result.Registered:
		output.Warn(s.out, "%s is not registered as an App ID on team %s: APNs refuses pushes for it until it is.", bundleID, result.TeamID)
		output.Hint(s.out, "Run `appwrite init apns --register-app-id`, or register it at %s.", appIDsPage)
	default:
		output.Warn(s.out, "Push Notifications is off for the App ID %s: APNs refuses pushes for it until it is on.", bundleID)
		output.Hint(s.out, "Run `appwrite init apns --register-app-id`, or turn it on at %s.", appIDsPage)
	}

	return nil
}

// needsTerminal says that signing in to Apple asked a question without a terminal.
func needsTerminal(unanswered *prompt.NonInteractiveError) string {
	return fmt.Sprintf("signing in to Apple asked %q, which needs an interactive terminal. %s and %s answer the Apple ID and password, but the two-factor code always needs one", unanswered.Message, appleIDVariable, applePasswordVariable)
}

// provisionedTeam is the team of an existing APNs provider for the bundle ID,
// else the one detected in the project, else none (signing in picks it).
func provisionedTeam(production, sandbox *messagingProvider, detected []string) string {
	for _, provider := range []*messagingProvider{production, sandbox} {
		if provider != nil && appleTeamID.MatchString(provider.credential("teamId")) {
			return provider.credential("teamId")
		}
	}
	if len(detected) == 1 {
		return detected[0]
	}

	return ""
}

// createApnsKey creates a key through the Apple Developer portal and saves it
// into the project folder before anything else happens, because Apple lets a
// key be downloaded only once.
func (s *pushSetup) createApnsKey(teamID string, options apnsOptions, environment apns.Environment) (string, string, string, error) {
	adapter, err := s.apnsAdapter(options.setup)
	if err != nil {
		return "", "", "", err
	}
	request := s.appleRequest(adapter, teamID, options.reset)
	s.logAppleSignIn(adapter, request)
	request.Name = "Appwrite Push " + time.Now().Format("20060102150405")
	request.Environment = environment
	key, err := apns.CreateKey(s.context(), adapter, request)
	if err == nil {
		s.appleID = key.AppleID
	}
	var unanswered *prompt.NonInteractiveError
	if errors.As(err, &unanswered) {
		return "", "", "", fmt.Errorf("%s. Without a terminal, pass --key-path with an existing .p8 instead of --create-key", needsTerminal(unanswered))
	}
	if err != nil {
		return "", "", "", err
	}

	directory := s.root
	if len(s.keyDirs) > 0 {
		directory = s.keyDirs[0]
	}
	path := filepath.Join(directory, "AuthKey_"+key.KeyID+".p8")
	file, err := os.OpenFile(path, os.O_WRONLY|os.O_CREATE|os.O_EXCL, 0o600)
	if err != nil {
		return "", "", "", fmt.Errorf("could not save the new key %s: %w", key.KeyID, err)
	}
	_, err = file.WriteString(key.P8)
	if closeErr := file.Close(); err == nil {
		err = closeErr
	}
	if err != nil {
		return "", "", "", fmt.Errorf("could not save the new key %s: %w", key.KeyID, err)
	}

	output.Success(s.out, "Created APNs key %s on team %s and saved it to %s.", key.KeyID, key.TeamID, s.display(path))
	output.Hint(s.out, "Apple lets you download this key only once. Keep the file somewhere safe and out of version control.")
	s.checkKeyLocation(path)

	return path, key.KeyID, key.TeamID, nil
}

func (s *pushSetup) acquireKey(what, automatic, browserLabel, flag, page, instructions string, accept func(string) bool) (string, bool, error) {
	found := s.projectKeys(accept)
	if len(found) == 1 {
		output.Log(s.out, "Using %s from the project folder.", s.display(found[0]))
		s.checkKeyLocation(found[0])

		return found[0], true, nil
	}
	if len(found) > 1 {
		options := make([]prompt.Option, 0, len(found))
		for _, path := range found {
			options = append(options, prompt.Option{Label: s.display(path), Value: path})
		}
		path, err := s.prompter.Choice(prompt.Choice{
			Message: fmt.Sprintf("Which %s should be used?", what),
			Options: options,
			Flag:    flag,
		})
		if err != nil {
			return "", false, err
		}
		s.checkKeyLocation(path)

		return path, true, nil
	}

	methods := []prompt.Option{
		{Label: browserLabel, Value: "browser"},
		{Label: "Use a file I already have", Value: "file"},
	}
	if automatic != "" {
		first := prompt.Option{Label: automatic, Value: "automatic"}
		methods = append([]prompt.Option{first}, methods...)
	}
	method, err := s.prompter.Choice(prompt.Choice{
		Message: fmt.Sprintf("How would you like to provide the %s?", what),
		Options: methods,
		Default: methods[0].Value,
		Flag:    flag,
	})
	if err != nil {
		return "", false, err
	}
	if method == "automatic" {
		return "", false, errAutomaticKey
	}

	if method == "file" {
		path, err := s.prompter.Text(prompt.Text{
			Message: fmt.Sprintf("Path to the %s", what),
			Flag:    flag,
			Validate: func(path string) error {
				if _, err := os.Stat(expandHome(path)); err != nil {
					return fmt.Errorf("no file at %s", path)
				}

				return nil
			},
		})
		if err != nil {
			return "", false, err
		}
		s.checkKeyLocation(expandHome(path))

		return path, false, nil
	}

	directories := append([]string{}, s.keyDirs...)
	if s.downloads != "" {
		if info, err := os.Stat(s.downloads); err == nil && info.IsDir() {
			directories = appendUnique(directories, s.downloads)
		}
	}
	if len(directories) == 0 {
		return "", false, fmt.Errorf("no folder to watch for the key. Pass %s instead", flag)
	}

	output.Log(s.out, "%s", instructions)
	output.Log(s.out, "Opening %s", page)
	s.open(page)
	if len(s.keyDirs) > 0 {
		output.Log(s.out, "Save the file into %s, or let your browser download it to %s. Waiting (Ctrl-C to cancel) ...",
			s.keyDirs[0], s.downloads)
	} else {
		output.Log(s.out, "Waiting for the file in %s (Ctrl-C to cancel) ...", s.downloads)
	}

	path, err := waitForKeyFile(directories, time.Now(), accept, downloadTimeout, downloadPoll)
	if err != nil {
		return "", false, fmt.Errorf("%w. Pass %s instead", err, flag)
	}
	output.Log(s.out, "Found %s", path)
	s.checkKeyLocation(path)

	return path, false, nil
}

func (s *pushSetup) projectKeys(accept func(string) bool) []string {
	var found []string
	for _, directory := range s.keyDirs {
		entries, err := os.ReadDir(directory)
		if err != nil {
			continue
		}
		for _, entry := range entries {
			path := filepath.Join(directory, entry.Name())
			if !entry.IsDir() && accept(path) {
				found = appendUnique(found, path)
			}
		}
	}

	return found
}

func (s *pushSetup) display(path string) string {
	for _, directory := range s.keyDirs {
		if relative, err := filepath.Rel(directory, path); err == nil && !strings.HasPrefix(relative, "..") {
			return relative
		}
	}

	return path
}

func (s *pushSetup) checkKeyLocation(path string) {
	absolute, err := filepath.Abs(path)
	if err != nil {
		return
	}
	git, err := exec.LookPath("git")
	if err != nil {
		return
	}
	check := exec.Command(git, "-C", filepath.Dir(absolute), "check-ignore", "-q", filepath.Base(absolute))
	if err := check.Run(); err != nil {
		var exit *exec.ExitError
		if errors.As(err, &exit) && exit.ExitCode() == 1 {
			output.Warn(s.out, "%s is inside a git repository and not ignored. Add it to .gitignore so the key is never committed.", s.display(absolute))
		}
	}
}

func waitForKeyFile(directories []string, since time.Time, accept func(string) bool, timeout, poll time.Duration) (string, error) {
	seen := map[string]time.Time{}
	for _, directory := range directories {
		entries, err := os.ReadDir(directory)
		if err != nil {
			continue
		}
		for _, entry := range entries {
			if info, err := entry.Info(); err == nil && info.ModTime().Before(since) {
				seen[filepath.Join(directory, entry.Name())] = info.ModTime()
			}
		}
	}

	deadline := time.Now().Add(timeout)
	for {
		for _, directory := range directories {
			entries, err := os.ReadDir(directory)
			if err != nil {
				continue
			}
			for _, entry := range entries {
				if entry.IsDir() {
					continue
				}
				info, err := entry.Info()
				if err != nil {
					continue
				}
				path := filepath.Join(directory, entry.Name())
				if previous, ok := seen[path]; ok && !info.ModTime().After(previous) {
					continue
				}
				if accept(path) {
					return path, nil
				}
			}
		}
		if time.Now().After(deadline) {
			return "", fmt.Errorf("no matching file appeared in %s within %s", strings.Join(directories, " or "), timeout)
		}
		time.Sleep(poll)
	}
}

func expandHome(path string) string {
	if strings.HasPrefix(path, "~/") {
		if home, err := os.UserHomeDir(); err == nil {
			return filepath.Join(home, path[2:])
		}
	}

	return path
}

func readApnsKey(path string) (string, error) {
	contents, err := os.ReadFile(expandHome(path))
	if err != nil {
		return "", err
	}
	block, _ := pem.Decode(contents)
	if block == nil || block.Type != "PRIVATE KEY" {
		return "", fmt.Errorf("%s is not an APNs auth key: expected a PEM 'PRIVATE KEY' block", path)
	}
	key, err := x509.ParsePKCS8PrivateKey(block.Bytes)
	if err != nil {
		return "", fmt.Errorf("%s is not an APNs auth key: %w", path, err)
	}
	ecKey, ok := key.(*ecdsa.PrivateKey)
	if !ok || ecKey.Curve != elliptic.P256() {
		return "", fmt.Errorf("%s is not an APNs auth key: expected a P-256 elliptic curve key", path)
	}

	return string(contents), nil
}

type messagingProvider struct {
	ID          string         `json:"$id"`
	Name        string         `json:"name"`
	Provider    string         `json:"provider"`
	Enabled     bool           `json:"enabled"`
	Credentials map[string]any `json:"credentials"`
	Options     map[string]any `json:"options"`
}

func (p messagingProvider) credential(name string) string {
	value, _ := p.Credentials[name].(string)

	return value
}

func (p messagingProvider) sandbox() bool {
	value, _ := p.Options["sandbox"].(bool)

	return value
}

func findProvider(providers []messagingProvider, match func(messagingProvider) bool) *messagingProvider {
	for index := range providers {
		if match(providers[index]) {
			return &providers[index]
		}
	}

	return nil
}

func (s *pushSetup) providers(kind string) ([]messagingProvider, error) {
	query := url.Values{"queries[]": []string{
		`{"method":"equal","attribute":"provider","values":["` + kind + `"]}`,
		`{"method":"limit","values":[100]}`,
	}}
	var list struct {
		Providers []messagingProvider `json:"providers"`
	}
	if err := s.api.Call("GET", "/messaging/providers?"+query.Encode(), nil, &list); err != nil {
		return nil, fmt.Errorf("could not list messaging providers: %w", err)
	}

	return list.Providers, nil
}

func (s *pushSetup) upsertProvider(kind string, existing *messagingProvider, body map[string]any) error {
	var saved messagingProvider
	if existing == nil {
		body["providerId"] = "unique()"
		if err := s.api.Call("POST", "/messaging/providers/"+kind, body, &saved); err != nil {
			return fmt.Errorf("could not create %s: %w", body["name"], err)
		}
		output.Success(s.out, "Created provider %s ( %s )", saved.Name, saved.ID)

		return nil
	}

	if err := s.api.Call("PATCH", "/messaging/providers/"+kind+"/"+url.PathEscape(existing.ID), body, &saved); err != nil {
		return fmt.Errorf("could not update %s: %w", existing.Name, err)
	}
	output.Success(s.out, "Updated provider %s ( %s )", saved.Name, saved.ID)

	return nil
}

func (s *pushSetup) configureXcode(detected appleApp, bundleID string) {
	targets := detected.targets(bundleID)
	inXcode, entitlements, infoPlists := false, 0, 0
	for _, target := range targets {
		if target.Project == "" {
			continue
		}
		inXcode = true
		for _, path := range target.Entitlements {
			entitlements++
			changed, err := editPlist(path, func(contents string) string {
				return ensurePlistString(contents, "aps-environment", "development")
			})
			s.reportEdit(path, "aps-environment", changed, err)
		}
		for _, path := range target.InfoPlists {
			infoPlists++
			changed, err := editPlist(path, func(contents string) string {
				return ensurePlistArrayValue(contents, "UIBackgroundModes", "remote-notification")
			})
			s.reportEdit(path, "the remote-notification background mode", changed, err)
		}
	}

	if inXcode && entitlements == 0 {
		output.Hint(s.out, "In Xcode, open Signing & Capabilities and add Push Notifications, so the app gets an aps-environment entitlement.")
	}
	if inXcode && infoPlists == 0 {
		output.Hint(s.out, "In Xcode, add Background Modes and tick Remote notifications.")
	}
	if detected.ExpoConfig != "" {
		output.Hint(s.out, "Expo regenerates ios/ on prebuild. In %s, set expo.ios.entitlements[\"aps-environment\"] to \"development\" and add \"remote-notification\" to expo.ios.infoPlist.UIBackgroundModes.",
			filepath.Base(detected.ExpoConfig))
	}
}

func (s *pushSetup) reportEdit(path, what string, changed bool, err error) {
	switch {
	case err != nil:
		output.Warn(s.out, "Could not add %s to %s: %s", what, path, err)
	case changed:
		output.Success(s.out, "Added %s to %s", what, path)
	}
}

func editPlist(path string, edit func(string) string) (bool, error) {
	contents, err := os.ReadFile(path)
	if err != nil {
		return false, err
	}
	original := string(contents)
	if !strings.Contains(original, "<plist") {
		return false, errors.New("only XML property lists can be edited")
	}
	updated := edit(original)
	if updated == original {
		return false, nil
	}
	info, err := os.Stat(path)
	if err != nil {
		return false, err
	}

	return true, os.WriteFile(path, []byte(updated), info.Mode().Perm())
}

func lineIndent(contents string, index int) (string, int) {
	lineStart := strings.LastIndex(contents[:index], "\n") + 1
	indent := contents[lineStart:index]
	if strings.TrimSpace(indent) != "" {
		return "", index
	}

	return indent, lineStart
}

func topLevelDict(contents string) (string, int, string, string) {
	if index := strings.Index(contents, "<dict/>"); index >= 0 && !strings.Contains(contents, "<dict>") {
		indent, _ := lineIndent(contents, index)
		contents = contents[:index] + "<dict>\n" + indent + "</dict>" + contents[index+len("<dict/>"):]
	}
	end := strings.LastIndex(contents, "</dict>")
	if end < 0 {
		return contents, -1, "", ""
	}
	closing, lineStart := lineIndent(contents, end)

	unit := "\t"
	if key := plistKeyLine.FindStringSubmatch(contents); key != nil && strings.HasPrefix(key[1], closing) && len(key[1]) > len(closing) {
		unit = key[1][len(closing):]
	}

	return contents, lineStart, closing + unit, unit
}

var plistKeyLine = regexp.MustCompile(`(?m)^([ \t]*)<key>`)

func ensurePlistString(contents, key, value string) string {
	if strings.Contains(contents, "<key>"+key+"</key>") {
		return contents
	}
	contents, at, indent, _ := topLevelDict(contents)
	if at < 0 {
		return contents
	}

	return contents[:at] + indent + "<key>" + key + "</key>\n" + indent + "<string>" + value + "</string>\n" + contents[at:]
}

func ensurePlistArrayValue(contents, key, value string) string {
	marker := "<key>" + key + "</key>"
	entry := "<string>" + value + "</string>"
	if index := strings.Index(contents, marker); index >= 0 {
		after := index + len(marker)
		rest := contents[after:]
		if empty := strings.Index(rest, "<array/>"); empty >= 0 && strings.TrimSpace(rest[:empty]) == "" {
			keyIndent, _ := lineIndent(contents, index)
			_, _, _, unit := topLevelDict(contents)
			at := after + empty

			return contents[:at] + "<array>\n" + keyIndent + unit + entry + "\n" + keyIndent + "</array>" + contents[at+len("<array/>"):]
		}
		open := strings.Index(rest, "<array>")
		closing := strings.Index(rest, "</array>")
		if open < 0 || closing < open || strings.TrimSpace(rest[:open]) != "" {
			return contents
		}
		if strings.Contains(rest[open:closing], entry) {
			return contents
		}
		keyIndent, _ := lineIndent(contents, index)
		_, _, _, unit := topLevelDict(contents)
		_, lineStart := lineIndent(contents, after+closing)
		if lineStart > 0 && contents[lineStart-1] != '\n' {
			return contents
		}

		return contents[:lineStart] + keyIndent + unit + entry + "\n" + contents[lineStart:]
	}

	contents, at, indent, unit := topLevelDict(contents)
	if at < 0 {
		return contents
	}

	return contents[:at] + indent + marker + "\n" + indent + "<array>\n" + indent + unit + entry + "\n" + indent + "</array>\n" + contents[at:]
}
