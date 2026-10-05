//go:build !browser

package cmd

import (
	"context"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rsa"
	"crypto/x509"
	"encoding/json"
	"encoding/pem"
	"errors"
	"fmt"
	"io"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

const (
	apnsKeyPage         = "https://developer.apple.com/account/resources/authkeys/add"
	fcmConsoleHome      = "https://console.firebase.google.com"
	fcmServiceAccounts  = "https://console.firebase.google.com/project/%s/settings/serviceaccounts/adminsdk"
	fcmSetupGuide       = "https://firebase.google.com/docs/android/setup#add-config-file"
	downloadTimeout     = 15 * time.Minute
	downloadPoll        = time.Second
	firebaseListTimeout = 30 * time.Second
)

var (
	apnsKeyFileName = regexp.MustCompile(`^AuthKey_([A-Z0-9]{10})\.p8$`)
	apnsKeyID       = regexp.MustCompile(`^[A-Z0-9]{10}$`)
)

type apnsOptions struct {
	keyPath  string
	keyID    string
	teamID   string
	bundleID string
}

type fcmOptions struct {
	keyPath       string
	projectID     string
	applicationID string
}

type pushSetup struct {
	api           *client.Client
	prompter      prompt.Prompter
	out           io.Writer
	root          string
	keyDirs       []string
	downloads     string
	open          func(string)
	firebase      func() ([]firebaseProject, error)
	console       *client.Client
	googleHosts   map[string]string
	googlePoll    time.Duration
	googleTimeout time.Duration
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

	return &pushSetup{
		api:           context.api,
		prompter:      context.prompter,
		out:           command.OutOrStdout(),
		root:          ".",
		keyDirs:       keyDirs,
		downloads:     downloads,
		open:          openBrowser,
		firebase:      listFirebaseProjects,
		console:       newConsoleForPush(),
		googlePoll:    2 * time.Second,
		googleTimeout: googleSignInTimeout,
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

	return command
}

func newInitFcmCommand() *cobra.Command {
	options := fcmOptions{}
	command := &cobra.Command{
		Use:   "fcm",
		Short: "Set up Firebase Cloud Messaging for Appwrite Push",
		RunE: func(command *cobra.Command, args []string) error {
			setup, err := newPushSetup(command)
			if err != nil {
				return err
			}
			detected, err := detectAndroid(setup.root)
			if err != nil {
				return err
			}

			return setup.fcm(options, detected)
		},
	}

	flags := command.Flags()
	flags.StringVar(&options.keyPath, "key-path", "", "Path to a Firebase service account JSON file")
	flags.StringVar(&options.projectID, "project-id", "", "Firebase project ID")
	flags.StringVar(&options.applicationID, "application-id", "", "Android application ID")

	return command
}

func newInitPushCommand() *cobra.Command {
	var skipApns, skipFcm bool
	command := &cobra.Command{
		Use:   "push",
		Short: "Set up background delivery for Appwrite Push on every platform in this project",
		RunE: func(command *cobra.Command, args []string) error {
			setup, err := newPushSetup(command)
			if err != nil {
				return err
			}

			return setup.push(skipApns, skipFcm)
		},
	}

	command.Flags().BoolVar(&skipApns, "skip-apns", false, "Do not set up APNs")
	command.Flags().BoolVar(&skipFcm, "skip-fcm", false, "Do not set up FCM")

	return command
}

func (s *pushSetup) push(skipApns, skipFcm bool) error {
	apple, err := detectApple(s.root)
	if err != nil {
		return err
	}
	android, err := detectAndroid(s.root)
	if err != nil {
		return err
	}

	runApns := apple.found() && !skipApns
	runFcm := android.found() && !skipFcm

	fmt.Fprintln(s.out, output.Heading("Detected"))
	fmt.Fprintf(s.out, "  iOS      %s\n", describeApple(apple, skipApns))
	fmt.Fprintf(s.out, "  Android  %s\n\n", describeAndroid(android, skipFcm))

	if !runApns && !runFcm {
		output.Log(s.out, "No iOS or Android app found here.")
		output.Hint(s.out, "Run '%s init apns' or '%s init fcm' with --key-path to set up a platform directly.",
			app.ExecutableName, app.ExecutableName)

		return nil
	}

	var failures []string
	if runApns {
		if err := s.apns(apnsOptions{}, apple); err != nil {
			s.reportStep("APNs", "apns", err)
			failures = append(failures, "APNs")
		}
	}
	if runFcm {
		if runApns {
			fmt.Fprintln(s.out)
		}
		if err := s.fcm(fcmOptions{}, android); err != nil {
			s.reportStep("FCM", "fcm", err)
			failures = append(failures, "FCM")
		}
	}

	if len(failures) > 0 {
		return fmt.Errorf("%s setup did not finish", strings.Join(failures, " and "))
	}

	return nil
}

func (s *pushSetup) reportStep(name, command string, err error) {
	if errors.Is(err, prompt.ErrAborted) {
		return
	}
	var unanswered *prompt.NonInteractiveError
	if errors.As(err, &unanswered) && unanswered.Flag != "" {
		output.Failure(s.out, "%s: %q needs an answer but there is no interactive terminal", name, unanswered.Message)
		output.Hint(s.out, "Run '%s init %s %s <value>' instead.", app.ExecutableName, command, unanswered.Flag)

		return
	}
	output.Failure(s.out, "%s: %s", name, err)
}

func describeFirebase(configs []firebaseConfig) string {
	parts := make([]string, 0, len(configs))
	for _, config := range configs {
		packages := strings.Join(config.Packages, ", ")
		if packages == "" {
			packages = "no Android apps"
		}
		parts = append(parts, config.Path+" has "+packages)
	}

	return strings.Join(parts, "; ")
}

func describeApple(apple appleApp, skipped bool) string {
	switch {
	case !apple.found():
		return "not found"
	case skipped:
		return "skipped (--skip-apns)"
	case len(apple.BundleIDs) == 0:
		return "found, bundle ID unknown"
	}

	return strings.Join(apple.BundleIDs, ", ")
}

func describeAndroid(android androidApp, skipped bool) string {
	switch {
	case !android.found():
		return "not found"
	case skipped:
		return "skipped (--skip-fcm)"
	}

	description := strings.Join(android.ApplicationIDs, ", ")
	if description == "" {
		description = "found, application ID unknown"
	}
	if projects := android.projectIDs(); len(projects) > 0 {
		label := " (Firebase project "
		if len(projects) > 1 {
			label = " (Firebase projects "
		}
		description += label + strings.Join(projects, ", ") + ")"
	}

	return description
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

	bundleID, err := s.pick(options.bundleID, detected.BundleIDs,
		"Which iOS bundle ID should receive push notifications?", "--bundle-id", requiredValue("bundle ID"))
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
	if production != nil && sandbox != nil && production.Enabled && sandbox.Enabled && options.keyPath == "" && !app.Flags().Force {
		output.Success(s.out, "APNs is already set up for %s.", bundleID)
		output.Hint(s.out, "Pass --force or --key-path to replace the key.")
		s.configureXcode(detected, bundleID)

		return nil
	}

	teams := detected.teams(bundleID)
	for _, provider := range []*messagingProvider{production, sandbox} {
		if provider != nil && len(teams) == 0 && appleTeamID.MatchString(provider.credential("teamId")) {
			teams = []string{provider.credential("teamId")}
		}
	}
	teamID, err := s.pick(options.teamID, teams,
		"What is your Apple Developer team ID?", "--team-id",
		matchesPattern(appleTeamID, "a team ID is 10 uppercase letters or digits"))
	if err != nil {
		return err
	}

	keyPath := options.keyPath
	if keyPath == "" {
		keyPath, _, err = s.acquireKey("APNs auth key (.p8)", "", "Create one in the Apple Developer portal", "--key-path", apnsKeyPage,
			"In the Apple Developer portal, create a key with Apple Push Notifications service (APNs) enabled and download it.",
			func(path string) bool {
				_, err := readApnsKey(path)

				return strings.HasSuffix(path, ".p8") && err == nil
			})
		if err != nil {
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

	for _, environment := range []struct {
		sandbox  bool
		name     string
		existing *messagingProvider
	}{
		{false, "APNs (" + bundleID + ")", production},
		{true, "APNs sandbox (" + bundleID + ")", sandbox},
	} {
		body := map[string]any{
			"name":      environment.name,
			"authKey":   key,
			"authKeyId": keyID,
			"teamId":    teamID,
			"bundleId":  bundleID,
			"sandbox":   environment.sandbox,
			"enabled":   true,
		}
		if err := s.upsertProvider("apns", environment.existing, body); err != nil {
			return err
		}
	}

	s.configureXcode(detected, bundleID)
	output.Success(s.out, "APNs is set up for %s.", bundleID)

	return nil
}

func (s *pushSetup) fcm(options fcmOptions, detected androidApp) error {
	output.Log(s.out, "Setting up FCM ...")

	configs := detected.Firebase
	if options.applicationID != "" || len(detected.ApplicationIDs) > 0 {
		applicationID, err := s.pick(options.applicationID, detected.ApplicationIDs,
			"Which Android application ID should receive push notifications?", "--application-id", requiredValue("application ID"))
		if err != nil {
			return err
		}
		if len(configs) > 0 {
			var matching []firebaseConfig
			for _, config := range configs {
				if contains(config.Packages, applicationID) {
					matching = append(matching, config)
				}
			}
			if len(matching) == 0 {
				return fmt.Errorf("no google-services.json here has an Android app with the application ID %s (%s). Add the app in the Firebase console and download google-services.json again",
					applicationID, describeFirebase(configs))
			}
			configs = matching
		}
	}

	candidates := detected.projectIDs()
	if len(detected.Firebase) > 0 {
		candidates = nil
		for _, config := range configs {
			candidates = appendUnique(candidates, config.ProjectID)
		}
	}

	projectID := options.projectID
	if projectID != "" && len(candidates) > 0 && !contains(candidates, projectID) {
		return fmt.Errorf("--project-id is %s but this app's Firebase configuration belongs to %s",
			projectID, strings.Join(candidates, ", "))
	}
	if projectID == "" && len(candidates) == 1 {
		projectID = candidates[0]
	}

	var account map[string]any
	keyPath := options.keyPath
	if keyPath != "" {
		parsed, err := readServiceAccount(keyPath, projectID)
		if err != nil {
			return err
		}
		s.checkKeyLocation(expandHome(keyPath))
		keyProject := parsed["project_id"].(string)
		if len(candidates) > 0 && !contains(candidates, keyProject) {
			return fmt.Errorf("%s belongs to Firebase project %s, not %s", keyPath, keyProject, strings.Join(candidates, " or "))
		}
		account = parsed
		projectID = keyProject
	}

	if projectID == "" && len(candidates) > 1 {
		var err error
		projectID, err = s.pick("", candidates, "Which Firebase project should send push notifications?", "--project-id", requiredValue("project ID"))
		if err != nil {
			return err
		}
	}
	if projectID == "" && account == nil {
		output.Log(s.out, "No Firebase configuration found in this project.")
	}
	if projectID == "" && account == nil && len(s.projectKeys(serviceAccountFile(""))) == 0 {
		var err error
		projectID, err = s.chooseFirebaseProject()
		if err != nil {
			return err
		}
	}

	var current *messagingProvider
	if projectID != "" {
		existing, err := s.providers("fcm")
		if err != nil {
			return err
		}
		current = findProvider(existing, func(provider messagingProvider) bool {
			return provider.projectID() == projectID
		})
		if current != nil && current.Enabled && keyPath == "" && !app.Flags().Force {
			output.Success(s.out, "FCM is already set up for Firebase project %s.", projectID)
			output.Hint(s.out, "Pass --force or --key-path to replace the service account key.")
			s.gradleHints(detected)

			return nil
		}
	}

	if account == nil && projectID != "" {
		for _, path := range s.projectKeys(serviceAccountFile("")) {
			if other, err := readServiceAccount(path, ""); err == nil && other["project_id"] != projectID {
				output.Warn(s.out, "Skipping %s: it belongs to Firebase project %s, not %s.", s.display(path), other["project_id"], projectID)
			}
		}
	}
	discovered := false
	var revoke func()
	saved := false
	defer func() {
		if revoke != nil && !saved {
			revoke()
		}
	}()
	if account == nil {
		page := fmt.Sprintf(fcmServiceAccounts, "_")
		instructions := "In the Firebase console, pick your project, then click 'Generate new private key' and download the file."
		if projectID != "" {
			page = fmt.Sprintf(fcmServiceAccounts, url.PathEscape(projectID))
			instructions = "In the Firebase console, click 'Generate new private key' and download the file."
		}
		automatic := ""
		if s.console != nil {
			automatic = "Sign in with Google and create it automatically"
		}
		for account == nil {
			path, fromProject, err := s.acquireKey("Firebase service account key (.json)", automatic, "Get one from the Firebase console", "--key-path",
				page, instructions, serviceAccountFile(projectID))
			if errors.Is(err, errAutomaticKey) {
				created, undo, err := s.provisionFCM(projectID, func(chosen string) error {
					return s.fcmAlreadySetUp(chosen)
				})
				if errors.Is(err, errProviderAlreadySetUp) {
					s.gradleHints(detected)

					return nil
				}
				if errors.Is(err, prompt.ErrAborted) {
					return err
				}
				if err != nil {
					output.Warn(s.out, "%s.", err)
					output.Log(s.out, "Provide the key another way instead.")
					automatic = ""

					continue
				}
				account, revoke = created, undo
				projectID = account["project_id"].(string)

				break
			}
			if err != nil {
				return err
			}
			discovered = fromProject
			account, err = readServiceAccount(path, projectID)
			if err != nil {
				return err
			}
		}
		if projectID == "" {
			projectID = account["project_id"].(string)
			output.Log(s.out, "Using Firebase project %s from the key.", projectID)
		}
	}

	if current == nil {
		existing, err := s.providers("fcm")
		if err != nil {
			return err
		}
		current = findProvider(existing, func(provider messagingProvider) bool {
			return provider.projectID() == projectID
		})
	}
	if discovered && current != nil && current.Enabled && !app.Flags().Force {
		output.Success(s.out, "FCM is already set up for Firebase project %s.", projectID)
		output.Hint(s.out, "Pass --force or --key-path to replace the service account key.")
		s.gradleHints(detected)

		return nil
	}

	body := map[string]any{
		"name":               "FCM (" + projectID + ")",
		"serviceAccountJSON": account,
		"enabled":            true,
	}
	if err := s.upsertProvider("fcm", current, body); err != nil {
		return err
	}
	saved = true

	s.gradleHints(detected)
	output.Success(s.out, "FCM is set up for Firebase project %s.", projectID)

	return nil
}

func (s *pushSetup) fcmAlreadySetUp(projectID string) error {
	if app.Flags().Force {
		return nil
	}
	existing, err := s.providers("fcm")
	if err != nil {
		return err
	}
	current := findProvider(existing, func(provider messagingProvider) bool {
		return provider.projectID() == projectID
	})
	if current == nil || !current.Enabled {
		return nil
	}
	output.Success(s.out, "FCM is already set up for Firebase project %s.", projectID)
	output.Hint(s.out, "Pass --force or --key-path to replace the service account key.")

	return errProviderAlreadySetUp
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

func serviceAccountFile(projectID string) func(string) bool {
	return func(path string) bool {
		if !strings.HasSuffix(path, ".json") {
			return false
		}
		_, err := readServiceAccount(path, projectID)

		return err == nil
	}
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

type firebaseProject struct {
	ProjectID   string `json:"projectId"`
	DisplayName string `json:"displayName"`
}

func listFirebaseProjects() ([]firebaseProject, error) {
	binary, err := exec.LookPath("firebase")
	if err != nil {
		return nil, err
	}
	ctx, cancel := context.WithTimeout(context.Background(), firebaseListTimeout)
	defer cancel()
	raw, err := exec.CommandContext(ctx, binary, "projects:list", "--json", "--non-interactive").Output()
	if err != nil {
		return nil, err
	}
	var listed struct {
		Status string            `json:"status"`
		Result []firebaseProject `json:"result"`
	}
	if err := json.Unmarshal(raw, &listed); err != nil {
		return nil, err
	}
	if listed.Status != "success" {
		return nil, errors.New("firebase projects:list did not succeed")
	}

	return listed.Result, nil
}

func (s *pushSetup) chooseFirebaseProject() (string, error) {
	if s.firebase == nil {
		return "", nil
	}
	projects, err := s.firebase()
	if err != nil || len(projects) == 0 {
		return "", nil
	}

	options := make([]prompt.Option, 0, len(projects)+1)
	for _, project := range projects {
		label := project.ProjectID
		if project.DisplayName != "" && project.DisplayName != project.ProjectID {
			label = project.DisplayName + " (" + project.ProjectID + ")"
		}
		options = append(options, prompt.Option{Label: label, Value: project.ProjectID})
	}
	options = append(options, prompt.Option{Label: "Another project (choose it in the Firebase console)", Value: ""})

	projectID, err := s.prompter.Choice(prompt.Choice{
		Message: "Which Firebase project should send push notifications?",
		Options: options,
		Default: projects[0].ProjectID,
		Filter:  len(options) > 8,
	})
	if errors.Is(err, prompt.ErrAborted) {
		return "", err
	}
	if err != nil {
		return "", nil
	}

	return projectID, nil
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

func readServiceAccount(path, projectID string) (map[string]any, error) {
	contents, err := os.ReadFile(expandHome(path))
	if err != nil {
		return nil, err
	}

	return parseServiceAccount(contents, path, projectID)
}

func parseServiceAccount(contents []byte, name, projectID string) (map[string]any, error) {
	var account map[string]any
	if err := json.Unmarshal(contents, &account); err != nil {
		return nil, fmt.Errorf("%s is not valid JSON: %w", name, err)
	}

	field := func(key string) string {
		value, _ := account[key].(string)

		return value
	}
	if field("type") != "service_account" {
		return nil, fmt.Errorf("%s is not a service account key: 'type' must be 'service_account'", name)
	}
	for _, key := range []string{"project_id", "client_email", "private_key"} {
		if field(key) == "" {
			return nil, fmt.Errorf("%s is not a service account key: '%s' is missing", name, key)
		}
	}
	block, _ := pem.Decode([]byte(field("private_key")))
	if block == nil {
		return nil, fmt.Errorf("%s has an unreadable 'private_key'", name)
	}
	key, err := x509.ParsePKCS8PrivateKey(block.Bytes)
	if err != nil {
		return nil, fmt.Errorf("%s has an unreadable 'private_key': %w", name, err)
	}
	if _, ok := key.(*rsa.PrivateKey); !ok {
		return nil, fmt.Errorf("%s has a 'private_key' that is not an RSA key", name)
	}
	if projectID != "" && field("project_id") != projectID {
		return nil, fmt.Errorf("%s belongs to Firebase project %s, not %s", name, field("project_id"), projectID)
	}

	return account, nil
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

func (p messagingProvider) projectID() string {
	account, _ := p.Credentials["serviceAccountJSON"].(map[string]any)
	value, _ := account["project_id"].(string)

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
		output.Hint(s.out, "Expo regenerates ios/ on prebuild. To keep these settings, add \"remote-notification\" to expo.ios.infoPlist.UIBackgroundModes in %s.",
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

func (s *pushSetup) gradleHints(detected androidApp) {
	if detected.ExpoConfig != "" {
		if !detected.ExpoGoogleServices {
			output.Hint(s.out, "Expo regenerates android/ on prebuild. Set expo.android.googleServicesFile in %s to your google-services.json.",
				filepath.Base(detected.ExpoConfig))
		}

		return
	}
	if len(detected.Modules) == 0 {
		return
	}
	if len(detected.Firebase) == 0 {
		output.Hint(s.out, "Download google-services.json for this app from the Firebase console into %s. See %s",
			detected.Modules[0], fcmSetupGuide)
	}
	if !detected.HasServicesPlugin {
		output.Hint(s.out, "Apply the Google services Gradle plugin in %s: id(\"com.google.gms.google-services\"). See %s",
			detected.Modules[0], fcmSetupGuide)
	}
	if !detected.HasMessaging {
		output.Hint(s.out, "Add Firebase Messaging to %s: implementation(\"com.google.firebase:firebase-messaging\"). Without it, Appwrite Push falls back to scheduled background delivery.",
			detected.Modules[0])
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
