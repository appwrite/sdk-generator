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

	return apns.Key{KeyID: a.KeyID, TeamID: team, P8: P8()}, nil
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

// AppIDAdapter is an Adapter that also prepares App IDs: it records each
// call and returns Result, or AppIDErr when set.
type AppIDAdapter struct {
	Adapter
	Result        apns.AppIDResult
	AppIDErr      error
	AppIDRequests []apns.AppIDRequest
	// AppIDSignIns are the sign-in requests each call came with.
	AppIDSignIns []apns.Request
}

// PrepareAppID records the call and returns Result or AppIDErr.
func (a *AppIDAdapter) PrepareAppID(_ context.Context, request apns.Request, appID apns.AppIDRequest) (apns.AppIDResult, error) {
	a.AppIDRequests = append(a.AppIDRequests, appID)
	a.AppIDSignIns = append(a.AppIDSignIns, request)
	if a.AppIDErr != nil {
		return apns.AppIDResult{}, a.AppIDErr
	}

	return a.Result, nil
}
