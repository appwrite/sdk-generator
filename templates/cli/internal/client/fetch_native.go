//go:build !js

package client

import "net/http"

func decorate(client *http.Client) *http.Client { return client }

func noteSelfSigned() {}
