//go:build js

package config

import "os"

// BrowserHome is the fallback when the embedding page does not define HOME.
const BrowserHome = "/home/appwrite"

func homeDir() (string, error) {
	if home, err := os.UserHomeDir(); err == nil && home != "" {
		return home, nil
	}

	return BrowserHome, nil
}
