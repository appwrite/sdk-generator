// Package client resolves what the terminal connects to from the flags, the environment, the
// project's appwrite.config.json and the cli's login, and connects.
package client

import (
	"context"
	"errors"
	"fmt"
	"sync"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/auth"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
)

// Options say which instance and project to open, and with which key. Without a key, the cli's
// login is used, and without a project it opens on the organizations the login can see.
type Options struct {
	Endpoint string
	Project  string
	Key      string
	// Global is the cli's prefs.json, which holds its login.
	Global *config.Global
	// SDKVersion is the cli's version, sent when an access token is renewed.
	SDKVersion string
}

// Info is what the header shows about the connection.
type Info struct {
	Endpoint string
	// Project is the project's id, empty on the console's organizations.
	Project string
	// Name is the project's name, empty when the instance cannot say it.
	Name    string
	Version string
	Cloud   bool
	RTT     time.Duration
	// As is who the interface works as: an api key, or the email of a console login.
	As string
	// Console says it works as a console login, which can open any project of its
	// organizations.
	Console bool
}

// asKey is what the header says of a connection made with an api key.
const asKey = "api key"

// resolve picks the credential: the key when there is one, else the cli's login, which is only
// sent to the instance it is of. A login's access token is renewed as the cli renews it.
func (o Options) resolve() (appwrite.Options, string, error) {
	a := appwrite.Options{Endpoint: o.Endpoint, Project: o.Project, Key: o.Key}
	var session *config.Object
	if o.Global != nil {
		session = o.Global.Current()
		a.SelfSigned = o.Global.CurrentBool(config.PreferenceSelfSigned)
	}
	cookie := session.GetString(config.PreferenceCookie)
	token := session.GetString(config.PreferenceAccessToken)
	sessionEndpoint := cmpOr(session.GetString(config.PreferenceEndpoint), config.DefaultEndpoint)
	if a.Key == "" && cookie == "" && token == "" {
		a.Key = session.GetString(config.PreferenceKey)
	}
	if a.Key != "" {
		if a.Project == "" {
			return a, "", errors.New("missing a project for the api key: pass --project-id, set APPWRITE_PROJECT_ID, or run it in a directory with an appwrite.config.json")
		}
		a.Endpoint = cmpOr(a.Endpoint, sessionEndpoint)
		return a, "", nil
	}
	if cookie == "" && token == "" {
		return a, "", errors.New("no login and no api key: run `appwrite login`, or set APPWRITE_API_KEY and pass --project-id")
	}
	if a.Endpoint != "" && !config.EndpointsMatch(a.Endpoint, sessionEndpoint) {
		return a, "", fmt.Errorf("the cli is signed in to %s, not %s: switch with `appwrite login --switch --endpoint %s`, or set APPWRITE_API_KEY for %s",
			sessionEndpoint, a.Endpoint, a.Endpoint, a.Endpoint)
	}
	a.Endpoint = sessionEndpoint
	if token != "" {
		a.Token = renewer(auth.NewAuthenticator(o.Global, o.SDKVersion))
	} else {
		a.Cookie = cookie
	}
	if a.Project == "" {
		a.Project = appwrite.ConsoleProject
	}
	return a, session.GetString(config.PreferenceEmail), nil
}

// renewer hands out the login's access token one caller at a time: the pages load at once, and
// a refresh token is spent when it is used, so a second refresh racing the first would fail.
func renewer(a *auth.Authenticator) func() (string, error) {
	var mu sync.Mutex
	return func() (string, error) {
		mu.Lock()
		defer mu.Unlock()
		return a.AccessToken(false)
	}
}

// Check reports what stops the options from connecting before anything is sent: a key without a
// project, no key and no login, or a login of another instance.
func (o Options) Check() error {
	_, _, err := o.resolve()
	return err
}

func cmpOr(a, b string) string {
	if a != "" {
		return a
	}
	return b
}

// connectTimeout bounds the reads that check the connection before the interface opens.
const connectTimeout = 15 * time.Second

// Connect makes a client and checks it reaches the instance: its version, which needs no key, and
// the project. An instance too old to describe the project, or a key without the scope to, still
// connects, with the project's id for its name; what the key cannot read, the pages say. A
// console login checks it is signed in by reading the account.
func Connect(ctx context.Context, o Options, agent string) (*appwrite.Client, Info, error) {
	opts, email, err := o.resolve()
	if err != nil {
		return nil, Info{}, err
	}
	opts.UserAgent = agent
	c, err := appwrite.New(opts)
	if err != nil {
		return nil, Info{}, err
	}
	ctx, cancel := context.WithTimeout(ctx, connectTimeout)
	defer cancel()
	info := Info{Endpoint: c.Endpoint(), Cloud: c.Cloud(), As: asKey, Console: c.Console()}
	if c.ProjectID() != appwrite.ConsoleProject {
		info.Project = c.ProjectID()
	}
	start := time.Now()
	info.Version, err = c.Version(ctx)
	info.RTT = time.Since(start)
	if err != nil {
		return nil, info, fmt.Errorf("reaching %s: %w", c.Endpoint(), err)
	}
	if info.Console {
		info.As = cmpOr(email, "console")
		return connectConsole(ctx, c, info)
	}
	project, err := c.Project(ctx)
	var apiErr *appwrite.Error
	switch {
	case err == nil:
		info.Name = project.Str("name")
	case appwrite.IsType(err, "project_not_found"):
		return nil, info, fmt.Errorf("opening project %s: %w: check --project-id, APPWRITE_PROJECT_ID or appwrite.config.json", info.Project, err)
	case errors.As(err, &apiErr) && apiErr.Code < 500:
	default:
		return nil, info, fmt.Errorf("opening project %s: %w", info.Project, err)
	}
	return c, info, nil
}

// connectConsole checks a console login is still signed in, and finds the name of the project it
// opens on, if it opens on one.
func connectConsole(ctx context.Context, c *appwrite.Client, info Info) (*appwrite.Client, Info, error) {
	console := c.AsProject(appwrite.ConsoleProject)
	if _, err := console.Account(ctx); err != nil {
		if appwrite.IsCode(err, 401) {
			return nil, info, fmt.Errorf("the login of %s is not signed in any more: %w: sign in again with `appwrite login`", info.As, err)
		}
		return nil, info, fmt.Errorf("reading the account of %s: %w", info.As, err)
	}
	if info.Project == "" {
		return c, info, nil
	}
	project, err := console.Get(ctx, appwrite.Ref{Kind: appwrite.Projects}, info.Project)
	if err != nil {
		return nil, info, fmt.Errorf("opening project %s: %w: check --project-id, APPWRITE_PROJECT_ID or appwrite.config.json", info.Project, err)
	}
	info.Name = project.Str("name")
	return c, info, nil
}
