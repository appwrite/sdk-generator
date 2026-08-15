//go:build !js

package config

import "os"

func homeDir() (string, error) {
	return os.UserHomeDir()
}
