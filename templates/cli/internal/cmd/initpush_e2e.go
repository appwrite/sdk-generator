//go:build e2e

package cmd

import (
	"os"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/appwrite"
)

func init() {
	e2eAppleHosts = func() appwrite.Hosts {
		host := os.Getenv("APPWRITE_APPLE_HOST")
		if host == "" {
			return appwrite.Hosts{}
		}

		return appwrite.Hosts{Idmsa: host, AppStoreConnect: host, Portal: host}
	}
}
