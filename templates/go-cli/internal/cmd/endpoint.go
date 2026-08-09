package cmd

import (
	"fmt"
	"net/url"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// verifyEndpoint checks that an endpoint is an Appwrite server before a
// password is typed into it.
func verifyEndpoint(endpoint string, selfSigned bool) error {
	parsed, err := url.Parse(endpoint)
	if err != nil || (parsed.Scheme != "http" && parsed.Scheme != "https") {
		return fmt.Errorf("invalid endpoint URL: %s", endpoint)
	}

	version := jsonx.NewObject()
	err = client.New(endpoint, app.Version).SetSelfSigned(selfSigned).
		Call("GET", "/health/version", nil, version)
	if err == nil && version.GetString("version") != "" {
		return nil
	}

	return fmt.Errorf(
		"invalid endpoint or your Appwrite server is not running as expected: %s",
		endpoint)
}

// regionalEndpoint prefixes the normalized endpoint host with a region.
func regionalEndpoint(endpoint, region string) string {
	normalized := config.NormalizeCloudConsoleEndpoint(endpoint)

	parsed, err := url.Parse(normalized)
	if err != nil {
		return normalized
	}
	parsed.Host = region + "." + parsed.Host

	return strings.TrimSuffix(parsed.String(), "/")
}

// isCloudEndpoint reports whether an endpoint is Appwrite Cloud.
func isCloudEndpoint(endpoint string) bool {
	_, ok := config.CloudBaseHost(endpoint)

	return ok
}
