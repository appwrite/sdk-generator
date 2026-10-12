// Package apnstest has a scripted APNs adapter for tests.
package apnstest

import (
	"context"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/x509"
	"encoding/pem"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

// Adapter is a fake that records each request and returns a fresh P-256 key,
// or Err when set.
type Adapter struct {
	AdapterName string
	KeyID       string
	// TeamID is used when the request names no team, as when an account
	// with one team signs in.
	TeamID   string
	Err      error
	Requests []apns.Request

	// AppIDResult is what PrepareAppID returns; zero means a registered App
	// ID with Push Notifications. Its team defaults like TeamID's.
	AppIDResult apns.AppIDResult
	AppIDErr    error
	// SignInNeeded makes a request limited to a saved sign-in get
	// apns.ErrSignInNeeded, as when nothing is saved.
	SignInNeeded bool
	// AppIDRequests and AppIDSignIns record each PrepareAppID call.
	AppIDRequests []apns.AppIDRequest
	AppIDSignIns  []apns.Request
}

// Name returns AdapterName, or "fake".
func (a *Adapter) Name() string {
	if a.AdapterName != "" {
		return a.AdapterName
	}

	return "fake"
}

// CreateKey records the request and returns a key or Err.
func (a *Adapter) CreateKey(_ context.Context, request apns.Request) (apns.Key, error) {
	a.Requests = append(a.Requests, request)
	if a.Err != nil {
		return apns.Key{}, a.Err
	}
	team := request.TeamID
	if team == "" {
		team = a.TeamID
	}

	appleID := request.AppleID
	if appleID == "" {
		appleID = "dev@example.com"
	}

	return apns.Key{KeyID: a.KeyID, TeamID: team, P8: P8(), AppleID: appleID}, nil
}

// PrepareAppID records the call and returns AppIDResult or AppIDErr.
func (a *Adapter) PrepareAppID(_ context.Context, request apns.Request, appID apns.AppIDRequest) (apns.AppIDResult, error) {
	a.AppIDRequests = append(a.AppIDRequests, appID)
	a.AppIDSignIns = append(a.AppIDSignIns, request)
	if request.SavedSignInOnly && a.SignInNeeded {
		return apns.AppIDResult{}, apns.ErrSignInNeeded
	}
	if a.AppIDErr != nil {
		return apns.AppIDResult{}, a.AppIDErr
	}
	result := a.AppIDResult
	if result == (apns.AppIDResult{}) {
		result = apns.AppIDResult{Registered: true, Push: true}
	}
	if result.TeamID == "" {
		result.TeamID = request.TeamID
	}
	if result.TeamID == "" {
		result.TeamID = a.TeamID
	}

	return result, nil
}

// P8 returns a new APNs-shaped private key in PEM form.
func P8() string {
	key, err := ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
	if err != nil {
		panic(err)
	}
	encoded, err := x509.MarshalPKCS8PrivateKey(key)
	if err != nil {
		panic(err)
	}

	return string(pem.EncodeToMemory(&pem.Block{Type: "PRIVATE KEY", Bytes: encoded}))
}
