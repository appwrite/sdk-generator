//go:build !browser

package cmd

import (
	"encoding/json"
	"io/fs"
	"os"
	"path/filepath"
	"regexp"
	"sort"
	"strings"
)

type firebaseConfig struct {
	Path      string
	ProjectID string
	Packages  []string
}

type androidApp struct {
	Modules            []string
	ApplicationIDs     []string
	Firebase           []firebaseConfig
	ProjectID          string
	ProjectIDSource    string
	ExpoConfig         string
	ExpoGoogleServices bool
	HasServicesPlugin  bool
	HasMessaging       bool
}

func (a androidApp) projectIDs() []string {
	var projects []string
	for _, config := range a.Firebase {
		projects = appendUnique(projects, config.ProjectID)
	}
	if len(projects) == 0 && a.ProjectID != "" {
		projects = []string{a.ProjectID}
	}

	return projects
}

const detectDepth = 4

var skippedDirectories = map[string]bool{
	"node_modules": true,
	"Pods":         true,
	".git":         true,
	"build":        true,
	"DerivedData":  true,
	".dart_tool":   true,
	".gradle":      true,
	"vendor":       true,
	".expo":        true,
}

func walkProject(root string, visit func(path string, entry fs.DirEntry) error) error {
	root = filepath.Clean(root)
	base := strings.Count(root, string(filepath.Separator))

	return filepath.WalkDir(root, func(path string, entry fs.DirEntry, err error) error {
		if err != nil {
			if path == root {
				return err
			}

			return nil
		}
		if entry.IsDir() && path != root {
			if skippedDirectories[entry.Name()] {
				return filepath.SkipDir
			}
			if strings.Count(path, string(filepath.Separator))-base > detectDepth {
				return filepath.SkipDir
			}
		}

		return visit(path, entry)
	})
}

type expoConfig struct {
	Expo struct {
		Android struct {
			Package            string `json:"package"`
			GoogleServicesFile string `json:"googleServicesFile"`
		} `json:"android"`
	} `json:"expo"`
}

func readExpoConfig(root string) (expoConfig, string) {
	for _, name := range []string{"app.json", "app.config.json"} {
		path := filepath.Join(root, name)
		contents, err := os.ReadFile(path)
		if err != nil {
			continue
		}
		var config expoConfig
		if json.Unmarshal(contents, &config) == nil {
			return config, path
		}
	}

	return expoConfig{}, ""
}

var (
	gradleApplicationPlugin = regexp.MustCompile(`com\.android\.application`)
	gradleApplicationID     = regexp.MustCompile(`\bapplicationId\s*(?:=\s*)?["']([^"']+)["']`)
	gradleNamespace         = regexp.MustCompile(`\bnamespace\s*(?:=\s*)?["']([^"']+)["']`)
	dartProjectID           = regexp.MustCompile(`projectId:\s*'([^']+)'`)
)

type googleServices struct {
	ProjectInfo struct {
		ProjectID string `json:"project_id"`
	} `json:"project_info"`
	Client []struct {
		ClientInfo struct {
			AndroidClientInfo struct {
				PackageName string `json:"package_name"`
			} `json:"android_client_info"`
		} `json:"client_info"`
	} `json:"client"`
}

func readGoogleServices(app *androidApp, path string) {
	for _, existing := range app.Firebase {
		if existing.Path == path {
			return
		}
	}
	contents, err := os.ReadFile(path)
	if err != nil {
		return
	}
	var services googleServices
	if json.Unmarshal(contents, &services) != nil || services.ProjectInfo.ProjectID == "" {
		return
	}

	config := firebaseConfig{Path: path, ProjectID: services.ProjectInfo.ProjectID}
	for _, client := range services.Client {
		if name := client.ClientInfo.AndroidClientInfo.PackageName; name != "" {
			config.Packages = appendUnique(config.Packages, name)
		}
	}
	app.Firebase = append(app.Firebase, config)
}

func readAndroidModule(app *androidApp, gradleFile string) {
	contents, err := os.ReadFile(gradleFile)
	if err != nil || !gradleApplicationPlugin.Match(contents) {
		return
	}
	module := filepath.Dir(gradleFile)
	app.Modules = append(app.Modules, module)

	if match := gradleApplicationID.FindSubmatch(contents); match != nil {
		app.ApplicationIDs = appendUnique(app.ApplicationIDs, string(match[1]))
	} else if match := gradleNamespace.FindSubmatch(contents); match != nil {
		app.ApplicationIDs = appendUnique(app.ApplicationIDs, string(match[1]))
	}

	text := string(contents)
	if strings.Contains(text, "com.google.gms.google-services") {
		app.HasServicesPlugin = true
	}
	if strings.Contains(text, "firebase-messaging") {
		app.HasMessaging = true
	}

	readGoogleServices(app, filepath.Join(module, "google-services.json"))
	flavours, _ := filepath.Glob(filepath.Join(module, "src", "*", "google-services.json"))
	for _, flavour := range flavours {
		readGoogleServices(app, flavour)
	}
}

type firebaseRC struct {
	Projects struct {
		Default string `json:"default"`
	} `json:"projects"`
}

func detectAndroid(root string) (androidApp, error) {
	app := androidApp{}

	err := walkProject(root, func(path string, entry fs.DirEntry) error {
		if !entry.IsDir() && (entry.Name() == "build.gradle" || entry.Name() == "build.gradle.kts") {
			readAndroidModule(&app, path)
		}

		return nil
	})
	if err != nil {
		return app, err
	}

	config, path := readExpoConfig(root)
	if config.Expo.Android.Package != "" {
		app.ExpoConfig = path
		app.ApplicationIDs = appendUnique(app.ApplicationIDs, config.Expo.Android.Package)
		if file := config.Expo.Android.GoogleServicesFile; file != "" {
			app.ExpoGoogleServices = true
			readGoogleServices(&app, filepath.Join(root, file))
		}
	}

	path = filepath.Join(root, "lib", "firebase_options.dart")
	if contents, err := os.ReadFile(path); err == nil {
		if match := dartProjectID.FindSubmatch(contents); match != nil {
			app.ProjectID, app.ProjectIDSource = string(match[1]), path
		}
	}

	if app.ProjectID == "" {
		path := filepath.Join(root, ".firebaserc")
		if contents, err := os.ReadFile(path); err == nil {
			var rc firebaseRC
			if json.Unmarshal(contents, &rc) == nil && rc.Projects.Default != "" {
				app.ProjectID, app.ProjectIDSource = rc.Projects.Default, path
			}
		}
	}

	sort.Strings(app.ApplicationIDs)

	return app, nil
}

func appendUnique(values []string, value string) []string {
	for _, existing := range values {
		if existing == value {
			return values
		}
	}

	return append(values, value)
}
