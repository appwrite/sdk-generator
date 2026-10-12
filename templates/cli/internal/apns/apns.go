// Package apns creates Apple Push Notification service (APNs) auth keys.
//
// Apple has no public API for creating APNs keys. The tools that automate it,
// fastlane's spaceship and @expo/apple-utils, sign in to the Apple Developer
// portal as a person would and call the portal's private API, and so does
// Appwrite's own implementation. Apple can change that API without notice, so
// each implementation is an Adapter, and which one runs is a setting rather
// than a release: when Apple breaks one, people switch to another.
//
// The package imports nothing from the CLI. Prompts and logging come in
// through the Request, so it can move to its own module and repository
// unchanged.
package apns

import (
	"context"
	"errors"
	"fmt"
	"regexp"
	"sort"
	"strings"
)

// Environment is the APNs environment a key works in.
type Environment string

// The environments Apple offers when creating a key.
const (
	EnvironmentAll        Environment = "all"
	EnvironmentProduction Environment = "production"
	EnvironmentSandbox    Environment = "sandbox"
)

// ParseEnvironment reads an environment name. Empty means all.
func ParseEnvironment(value string) (Environment, error) {
	switch environment := Environment(strings.ToLower(strings.TrimSpace(value))); environment {
	case "", EnvironmentAll:
		return EnvironmentAll, nil
	case EnvironmentProduction, EnvironmentSandbox:
		return environment, nil
	default:
		return "", fmt.Errorf("unknown APNs environment %q: use production, sandbox or all", value)
	}
}

// Key is a newly created APNs key. Apple lets its contents be downloaded once.
type Key struct {
	KeyID  string
	TeamID string
	P8     string
}

// ExistingKey is a key already on the team.
type ExistingKey struct {
	ID        string
	Name      string
	CanRevoke bool
}

// MaxKeysError means the team already has as many APNs keys as Apple allows.
// Keys lists the team's keys when the adapter could read them.
type MaxKeysError struct {
	Keys []ExistingKey
}

func (e *MaxKeysError) Error() string {
	return "the Apple team already has the maximum number of APNs keys"
}

var (
	// ErrUnavailable means the adapter cannot run here, for example because
	// the runtime it needs is missing. Nothing was sent to Apple.
	ErrUnavailable = errors.New("this APNs key setup is not available here")
	// ErrInvalidCredentials means Apple refused the Apple ID or password.
	ErrInvalidCredentials = errors.New("Apple did not accept the Apple ID and password")
	// ErrSignInNeeded means a request with SavedSignInOnly found no saved
	// sign-in it could use. Nothing was asked.
	ErrSignInNeeded = errors.New("signing in to Apple is needed")
)

// Asker asks the person at the terminal.
type Asker interface {
	// Ask reads one line. secret hides what is typed.
	Ask(question string, secret bool) (string, error)
	// Choose returns the index of the chosen option.
	Choose(question string, options []string) (int, error)
}

// Request describes the key to create.
type Request struct {
	// TeamID is the Apple Developer team that owns the key. Empty lets the
	// adapter use the account's only team, or ask which one.
	TeamID      string
	Name        string
	Environment Environment
	// AppleID and Password answer the sign-in questions when set.
	AppleID  string
	Password string
	// SessionDir is where an adapter may keep a signed-in session between
	// runs, readable by the user only.
	SessionDir string
	// Reset signs in to Apple again instead of reusing a saved sign-in, and
	// the new one replaces it.
	Reset bool
	// SavedSignInOnly uses a saved sign-in only: without a valid one the
	// adapter returns ErrSignInNeeded instead of asking.
	SavedSignInOnly bool
	Asker           Asker
	// Log prints a line for the person running the command.
	Log func(format string, args ...any)
}

// Adapter creates APNs keys, and prepares App IDs, one way.
type Adapter interface {
	// Name is how people pick the adapter, for example "appwrite".
	Name() string
	// CreateKey signs in, creates the key and downloads it. It returns
	// ErrUnavailable when it cannot run here, ErrInvalidCredentials when
	// Apple refuses the sign-in, and a *MaxKeysError when the team has no
	// free key slot.
	CreateKey(ctx context.Context, request Request) (Key, error)
	// PrepareAppID signs in like CreateKey and makes sure the bundle ID is an
	// App ID with Push Notifications: APNs refuses pushes for any other
	// bundle ID (TopicDisallowed), and a key alone does not register it. It
	// registers the App ID when the team has none and turns on Push
	// Notifications when it is off, asking first when appID.Ask is set
	// (ConfirmAppIDChange words the questions). With
	// request.SavedSignInOnly and no saved sign-in it returns
	// ErrSignInNeeded.
	PrepareAppID(ctx context.Context, request Request, appID AppIDRequest) (AppIDResult, error)
}

// AppIDRequest names the App ID to prepare.
type AppIDRequest struct {
	BundleID string
	// Ask asks before registering the App ID or turning on Push
	// Notifications. Without it the changes are made directly.
	Ask bool
}

// AppIDResult is what preparing an App ID found and changed.
type AppIDResult struct {
	TeamID string
	// Registered and Push describe the App ID after the call.
	Registered bool
	Push       bool
	// Created and PushTurnedOn are the changes the call made.
	Created      bool
	PushTurnedOn bool
}

// ConfirmAppIDChange asks before an App ID change: registering the bundle ID
// (with Push Notifications) when registered is false, else turning Push
// Notifications on. Every adapter asks with these words.
func ConfirmAppIDChange(asker Asker, bundleID, teamID string, registered bool) (bool, error) {
	question := fmt.Sprintf("%s is not registered as an App ID on team %s, so APNs refuses pushes for it. Register it with Push Notifications?", bundleID, teamID)
	if registered {
		question = fmt.Sprintf("Push Notifications is off for %s, so APNs refuses pushes for it. Turn it on?", bundleID)
	}
	choice, err := asker.Choose(question, []string{"Yes", "No"})

	return choice == 0, err
}

// AppIDName is an App ID's description in the portal, which allows only
// letters, digits and spaces.
func AppIDName(bundleID string) string {
	name := strings.Map(func(r rune) rune {
		if r >= 'a' && r <= 'z' || r >= 'A' && r <= 'Z' || r >= '0' && r <= '9' {
			return r
		}

		return ' '
	}, bundleID)

	return strings.Join(strings.Fields(name), " ")
}

var bundleIDPattern = regexp.MustCompile(`^[A-Za-z0-9.-]+$`)

// PrepareAppID checks the request, runs the adapter, and checks what it
// returned, so every adapter is held to the same contract.
func PrepareAppID(ctx context.Context, adapter Adapter, request Request, appID AppIDRequest) (AppIDResult, error) {
	if !bundleIDPattern.MatchString(appID.BundleID) {
		return AppIDResult{}, fmt.Errorf("%q is not a bundle ID: a bundle ID has letters, digits, hyphens and dots", appID.BundleID)
	}
	if request.TeamID != "" && !teamIDPattern.MatchString(request.TeamID) {
		return AppIDResult{}, fmt.Errorf("%q is not an Apple team ID: a team ID is 10 uppercase letters or digits", request.TeamID)
	}
	if request.Log == nil {
		request.Log = func(string, ...any) {}
	}

	result, err := adapter.PrepareAppID(ctx, request, appID)
	if err != nil {
		return result, err
	}
	switch {
	case !teamIDPattern.MatchString(result.TeamID):
		return result, fmt.Errorf("the %s APNs setup returned an invalid team ID %q", adapter.Name(), result.TeamID)
	case request.TeamID != "" && result.TeamID != request.TeamID:
		return result, fmt.Errorf("the %s APNs setup checked the App ID on team %s, not %s", adapter.Name(), result.TeamID, request.TeamID)
	case (result.Created || result.Push) && !result.Registered, result.PushTurnedOn && !result.Push:
		return result, fmt.Errorf("the %s APNs setup returned an inconsistent App ID result %+v", adapter.Name(), result)
	}

	return result, nil
}

var (
	keyIDPattern  = regexp.MustCompile(`^[A-Z0-9]{10}$`)
	teamIDPattern = regexp.MustCompile(`^[A-Z0-9]{10}$`)
)

// CreateKey checks the request, runs the adapter, and checks what it returned,
// so every adapter is held to the same contract.
func CreateKey(ctx context.Context, adapter Adapter, request Request) (Key, error) {
	environment, err := ParseEnvironment(string(request.Environment))
	if err != nil {
		return Key{}, err
	}
	request.Environment = environment
	if strings.TrimSpace(request.Name) == "" {
		return Key{}, errors.New("an APNs key needs a name")
	}
	if request.TeamID != "" && !teamIDPattern.MatchString(request.TeamID) {
		return Key{}, fmt.Errorf("%q is not an Apple team ID: a team ID is 10 uppercase letters or digits", request.TeamID)
	}
	if request.Log == nil {
		request.Log = func(string, ...any) {}
	}

	key, err := adapter.CreateKey(ctx, request)
	if err != nil {
		return Key{}, err
	}
	switch {
	case !keyIDPattern.MatchString(key.KeyID):
		return Key{}, fmt.Errorf("the %s APNs setup returned an invalid key ID %q", adapter.Name(), key.KeyID)
	case !teamIDPattern.MatchString(key.TeamID):
		return Key{}, fmt.Errorf("the %s APNs setup returned an invalid team ID %q", adapter.Name(), key.TeamID)
	case request.TeamID != "" && key.TeamID != request.TeamID:
		return Key{}, fmt.Errorf("the %s APNs setup created key %s on team %s, not %s", adapter.Name(), key.KeyID, key.TeamID, request.TeamID)
	case !strings.Contains(key.P8, "PRIVATE KEY"):
		return Key{}, fmt.Errorf("the %s APNs setup created key %s but returned no .p8 contents", adapter.Name(), key.KeyID)
	}

	return key, nil
}

// Registry holds the adapters a build offers, by name.
type Registry struct {
	adapters map[string]Adapter
}

// NewRegistry returns a registry holding adapters.
func NewRegistry(adapters ...Adapter) *Registry {
	registry := &Registry{adapters: map[string]Adapter{}}
	for _, adapter := range adapters {
		registry.adapters[adapter.Name()] = adapter
	}

	return registry
}

// Names lists the registered adapters in order.
func (r *Registry) Names() []string {
	names := make([]string, 0, len(r.adapters))
	for name := range r.adapters {
		names = append(names, name)
	}
	sort.Strings(names)

	return names
}

// Get returns the adapter called name.
func (r *Registry) Get(name string) (Adapter, error) {
	name = strings.ToLower(strings.TrimSpace(name))
	if adapter, ok := r.adapters[name]; ok {
		return adapter, nil
	}
	if len(r.adapters) == 0 {
		return nil, fmt.Errorf("APNs key setup %q is not available: this build has none", name)
	}

	return nil, fmt.Errorf("unknown APNs key setup %q: use one of %s", name, strings.Join(r.Names(), ", "))
}
