// Package appwrite is Appwrite's own APNs key setup, in Go with no runtime to
// install.
//
// It signs in the way the Apple Developer portal's web pages do, with the
// person's Apple ID, password and two-factor code, and calls the portal's
// private API. The sign-in follows spaceship (fastlane), which keeps up with
// Apple's changes in readable code, and the key calls follow
// @expo/apple-utils.
package appwrite

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

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

// Adapter creates APNs keys with Appwrite's own sign-in.
type Adapter struct {
	// For tests. Zero values mean Apple's servers and the real clock.
	HTTP   *http.Client
	Hosts  Hosts
	Random io.Reader
	Now    func() time.Time
}

// New returns the adapter with Apple's servers.
func New() *Adapter {
	return &Adapter{}
}

// Name is how people pick this setup.
func (a *Adapter) Name() string {
	return "appwrite"
}

// CreateKey signs in, creates a team-scoped APNs key and downloads it. The
// signed-in session is kept in request.SessionDir, when set, so later runs
// skip two-factor.
func (a *Adapter) CreateKey(ctx context.Context, request apns.Request) (apns.Key, error) {
	portal, err := a.connect(ctx, request)
	if err != nil {
		return apns.Key{}, err
	}
	defer portal.saveSession()

	teamID, err := portal.chooseTeam(ctx, request.TeamID)
	if err != nil {
		return apns.Key{}, err
	}
	existing, err := portal.keys(ctx, teamID)
	if err != nil {
		return apns.Key{}, err
	}
	keyID, err := portal.createKey(ctx, teamID, request.Name, request.Environment)
	if errors.Is(err, errMaxKeys) {
		return apns.Key{}, &apns.MaxKeysError{Keys: existing}
	}
	if err != nil {
		return apns.Key{}, err
	}
	p8, err := portal.downloadKey(ctx, teamID, keyID)
	if err != nil {
		return apns.Key{}, fmt.Errorf("created key %s but could not download it, and Apple allows only one download. Revoke it in the Apple Developer portal: %w", keyID, err)
	}

	return apns.Key{KeyID: keyID, TeamID: teamID, P8: p8}, nil
}

func sessionPath(dir string) string {
	if dir == "" {
		return ""
	}

	return filepath.Join(dir, "session.json")
}

// saveSession keeps the cookies for the next run. Without a session folder
// the session lives only as long as this run.
func (p *portalClient) saveSession() {
	if path := sessionPath(p.dir); path != "" {
		if err := p.jar.save(path); err != nil {
			p.log("Could not save the Apple sign-in, so the next run asks again: %v", err)
		}
	}
}

// connect returns a signed-in portal client, reusing the saved session when
// it is still valid and belongs to the Apple ID being used.
func (a *Adapter) connect(ctx context.Context, request apns.Request) (*portalClient, error) {
	if request.SessionDir != "" {
		if err := os.MkdirAll(request.SessionDir, 0o700); err != nil {
			return nil, err
		}
	}
	httpClient := a.HTTP
	if httpClient == nil {
		httpClient = &http.Client{Timeout: time.Minute}
	}
	jar := newSavedJar()
	if path := sessionPath(request.SessionDir); path != "" {
		jar.load(path)
	}
	withJar := *httpClient
	withJar.Jar = jar
	logf := request.Log
	if logf == nil {
		logf = func(string, ...any) {}
	}
	portal := &portalClient{
		http: &withJar, jar: jar, hosts: a.Hosts, dir: request.SessionDir, asker: request.Asker, log: logf,
		random: a.Random, now: a.Now,
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

	accountName := strings.TrimSpace(request.AppleID)
	if session, err := portal.session(ctx); err == nil && session != nil {
		if accountName == "" || strings.EqualFold(session.User.EmailAddress, accountName) {
			logf("Using the saved Apple sign-in for %s.", session.User.EmailAddress)

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
		answer, err := request.Asker.Ask("Apple ID (email)", false)
		if err != nil {
			return nil, err
		}
		accountName = strings.TrimSpace(answer)
	}
	password := request.Password
	if password == "" {
		answer, err := request.Asker.Ask("Password for "+accountName, true)
		if err != nil {
			return nil, err
		}
		password = answer
	}

	if err := portal.signIn(ctx, accountName, password); err != nil {
		return nil, err
	}
	// Saved as soon as Apple accepts the sign-in, so a failure after this
	// point does not cost another two-factor code.
	portal.saveSession()
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
