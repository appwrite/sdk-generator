// Package apple creates APNs keys through the Apple Developer portal.
//
// Apple has no public API for APNs keys, so this signs in the way the
// portal's web pages do, with the person's Apple ID, password and two-factor
// code, and calls the portal's private API. The sign-in follows spaceship
// (fastlane) and the key calls follow @expo/apple-utils, the two tools that
// do the same job. Apple can change either without notice, so callers keep
// another way to get a key.
package apple

import (
	"context"
	"crypto/rand"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strings"
	"time"
)

// Key is a newly created APNs key. Apple lets its contents be downloaded once.
type Key struct {
	KeyID  string
	TeamID string
	P8     string
}

// ExistingKey is a key already on the team, listed when no new one fits.
type ExistingKey struct {
	ID        string `json:"keyId"`
	Name      string `json:"keyName"`
	CanRevoke bool   `json:"canRevoke"`
}

// MaxKeysError means the team already has as many APNs keys as Apple allows.
type MaxKeysError struct {
	Keys []ExistingKey
}

func (e *MaxKeysError) Error() string {
	return "the Apple team already has the maximum number of APNs keys"
}

// Asker asks the person at the terminal.
type Asker interface {
	// Ask reads one line. secret hides what is typed.
	Ask(question string, secret bool) (string, error)
	// Choose returns the index of the chosen option.
	Choose(question string, options []string) (int, error)
}

// Environment variables that answer the Apple ID and password questions.
const (
	AppleIDVariable       = "APPWRITE_APPLE_ID"
	ApplePasswordVariable = "APPWRITE_APPLE_PASSWORD"
)

// Client signs in to the Apple Developer portal and creates keys.
type Client struct {
	// Dir keeps the signed-in session between runs, readable by the user only.
	Dir   string
	Asker Asker
	// Log prints a line for the person running the command.
	Log    func(format string, args ...any)
	Getenv func(string) string

	// For tests. Zero values mean Apple's servers and the real clock.
	HTTP   *http.Client
	Hosts  Hosts
	Random io.Reader
	Now    func() time.Time
}

// Environment is the APNs environment a key works in.
type Environment string

// The environments Apple offers when creating a key. Both tools that
// automate this only ever send EnvironmentAll; the other two are the values
// the portal's choices stand for.
const (
	EnvironmentAll        Environment = "all"
	EnvironmentProduction Environment = "production"
	EnvironmentSandbox    Environment = "sandbox"
)

// CreateKey signs in, creates a team-scoped APNs key named name for
// environment and downloads it. An empty teamID uses the account's only team,
// or asks which one.
func (c Client) CreateKey(ctx context.Context, teamID, name string, environment Environment) (Key, error) {
	switch environment {
	case EnvironmentAll, EnvironmentProduction, EnvironmentSandbox:
	default:
		return Key{}, fmt.Errorf("unknown APNs environment %q", environment)
	}
	portal, err := c.connect(ctx)
	if err != nil {
		return Key{}, err
	}
	defer portal.jar.save(c.sessionPath())

	teamID, err = portal.chooseTeam(ctx, teamID)
	if err != nil {
		return Key{}, err
	}
	existing, err := portal.keys(ctx, teamID)
	if err != nil {
		return Key{}, err
	}
	keyID, err := portal.createKey(ctx, teamID, name, environment)
	if errors.Is(err, errMaxKeys) {
		return Key{}, &MaxKeysError{Keys: existing}
	}
	if err != nil {
		return Key{}, err
	}
	p8, err := portal.downloadKey(ctx, teamID, keyID)
	if err != nil {
		return Key{}, fmt.Errorf("created key %s but could not download it, and Apple allows only one download. Revoke it in the Apple Developer portal: %w", keyID, err)
	}

	return Key{KeyID: keyID, TeamID: teamID, P8: p8}, nil
}

func (c Client) sessionPath() string {
	return filepath.Join(c.Dir, "session.json")
}

func (c Client) logf(format string, args ...any) {
	if c.Log != nil {
		c.Log(format, args...)
	}
}

func (c Client) env(name string) string {
	if c.Getenv == nil {
		return os.Getenv(name)
	}

	return c.Getenv(name)
}

// connect returns a signed-in portal client, reusing the saved session when
// it is still valid and belongs to the Apple ID being used.
func (c Client) connect(ctx context.Context) (*portalClient, error) {
	if err := os.MkdirAll(c.Dir, 0o700); err != nil {
		return nil, err
	}
	httpClient := c.HTTP
	if httpClient == nil {
		httpClient = &http.Client{Timeout: time.Minute}
	}
	jar := newSavedJar()
	jar.load(c.sessionPath())
	withJar := *httpClient
	withJar.Jar = jar
	portal := &portalClient{
		http: &withJar, jar: jar, hosts: c.Hosts, dir: c.Dir, asker: c.Asker, log: c.logf,
		random: c.Random, now: c.Now,
	}
	if portal.hosts == (Hosts{}) {
		portal.hosts = DefaultHosts
	}
	if portal.random == nil {
		portal.random = rand.Reader
	}
	if portal.now == nil {
		portal.now = time.Now
	}

	accountName := c.env(AppleIDVariable)
	if session, err := portal.session(ctx); err == nil && session != nil {
		if accountName == "" || strings.EqualFold(session.User.EmailAddress, accountName) {
			c.logf("Using the saved Apple sign-in for %s.", session.User.EmailAddress)

			return portal, nil
		}
		// Another Apple ID's session: none of it applies.
		jar = newSavedJar()
		withJar.Jar = jar
		portal.jar = jar
	}
	// An expired session's cookies stay: among them is the one that marks
	// this machine as trusted, which lets Apple skip two-factor on sign-in.
	if err := portal.loadWidgetKey(ctx); err != nil {
		return nil, err
	}

	if accountName == "" {
		answer, err := c.Asker.Ask("Apple ID (email)", false)
		if err != nil {
			return nil, err
		}
		accountName = strings.TrimSpace(answer)
	} else {
		c.logf("Using the Apple ID from %s.", AppleIDVariable)
	}
	password := c.env(ApplePasswordVariable)
	if password == "" {
		answer, err := c.Asker.Ask("Password for "+accountName, true)
		if err != nil {
			return nil, err
		}
		password = answer
	} else {
		c.logf("Using the Apple ID password from %s.", ApplePasswordVariable)
	}

	if err := portal.signIn(ctx, accountName, password); err != nil {
		return nil, err
	}
	// Saved as soon as Apple accepts the sign-in, so a failure after this
	// point does not cost another two-factor code.
	if err := portal.jar.save(c.sessionPath()); err != nil {
		c.logf("Could not save the Apple sign-in, so the next run asks again: %v", err)
	}
	session, err := portal.session(ctx)
	if err != nil {
		return nil, fmt.Errorf("could not reach App Store Connect: %w", err)
	}
	if session == nil {
		return nil, errors.New("Apple signed you in but App Store Connect refused the session. Sign in once at https://appstoreconnect.apple.com to accept any pending agreements, then try again")
	}

	return portal, nil
}

// chooseTeam checks teamID against the account's teams, or picks one.
func (p *portalClient) chooseTeam(ctx context.Context, teamID string) (string, error) {
	teams, err := p.teams(ctx)
	if errors.Is(err, errSessionExpired) {
		return "", errors.New("the Apple session expired. Run the command again to sign in")
	}
	if err != nil {
		return "", err
	}
	if len(teams) == 0 {
		return "", errors.New("this Apple ID belongs to no Apple Developer team. APNs keys need a paid Apple Developer Program membership")
	}
	if teamID != "" {
		ids := make([]string, len(teams))
		for index, team := range teams {
			if team.ID == teamID {
				return teamID, nil
			}
			ids[index] = team.ID
		}

		return "", fmt.Errorf("this Apple ID is not on team %s. Its teams are %s", teamID, strings.Join(ids, ", "))
	}
	if len(teams) == 1 {
		p.log("Using Apple team %s (%s).", teams[0].Name, teams[0].ID)

		return teams[0].ID, nil
	}
	labels := make([]string, len(teams))
	for index, team := range teams {
		labels[index] = fmt.Sprintf("%s (%s)", team.Name, team.ID)
	}
	chosen, err := p.asker.Choose("Which Apple Developer team should own the key?", labels)
	if err != nil {
		return "", err
	}

	return teams[chosen].ID, nil
}
