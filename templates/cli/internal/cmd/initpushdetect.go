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

type appleApp struct {
	Projects     []string
	BundleIDs    []string
	TeamIDs      []string
	Entitlements []string
	InfoPlists   []string
	ExpoConfig   string
}

func (a appleApp) found() bool {
	return len(a.Projects) > 0 || a.ExpoConfig != ""
}

type androidApp struct {
	Modules            []string
	ApplicationIDs     []string
	GoogleServices     string
	ProjectID          string
	ProjectIDSource    string
	FirebasePackages   []string
	ExpoConfig         string
	ExpoGoogleServices bool
	HasServicesPlugin  bool
	HasMessaging       bool
}

func (a androidApp) found() bool {
	return len(a.Modules) > 0 || a.ExpoConfig != ""
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

var (
	pbxBuildSettings = regexp.MustCompile(`(?s)buildSettings = \{(.*?)\n\t\t\t\};`)
	pbxSetting       = regexp.MustCompile(`(?m)^\s*([A-Za-z_][A-Za-z0-9_]*) = (.*?);\s*$`)
	appleTeamID      = regexp.MustCompile(`^[A-Z0-9]{10}$`)
)

func pbxValue(value string) string {
	value = strings.TrimSpace(value)
	if len(value) >= 2 && strings.HasPrefix(value, `"`) && strings.HasSuffix(value, `"`) {
		value = value[1 : len(value)-1]
	}

	return value
}

func rfc1034(value string) string {
	var builder strings.Builder
	for _, character := range value {
		switch {
		case character >= 'a' && character <= 'z', character >= 'A' && character <= 'Z',
			character >= '0' && character <= '9', character == '-', character == '.':
			builder.WriteRune(character)
		default:
			builder.WriteRune('-')
		}
	}

	return builder.String()
}

func resolveBundleID(bundleID string, settings map[string]string) string {
	name := settings["PRODUCT_NAME"]
	if name != "" && !strings.Contains(name, "$(") {
		bundleID = strings.ReplaceAll(bundleID, "$(PRODUCT_NAME:rfc1034identifier)", rfc1034(name))
		bundleID = strings.ReplaceAll(bundleID, "${PRODUCT_NAME:rfc1034identifier}", rfc1034(name))
		bundleID = strings.ReplaceAll(bundleID, "$(PRODUCT_NAME)", name)
		bundleID = strings.ReplaceAll(bundleID, "${PRODUCT_NAME}", name)
	}
	if strings.Contains(bundleID, "$") {
		return ""
	}

	return bundleID
}

func isTestTarget(settings map[string]string) bool {
	if settings["TEST_HOST"] != "" || settings["BUNDLE_LOADER"] != "" || settings["TEST_TARGET_NAME"] != "" {
		return true
	}
	bundleID := settings["PRODUCT_BUNDLE_IDENTIFIER"]

	return strings.HasSuffix(bundleID, "Tests") || strings.HasSuffix(bundleID, "UITests")
}

func projectPath(projectDir, value string) string {
	value = strings.TrimPrefix(value, "$(SRCROOT)/")
	value = strings.TrimPrefix(value, "${SRCROOT}/")
	value = strings.TrimPrefix(value, "$(PROJECT_DIR)/")
	if strings.Contains(value, "$") {
		return ""
	}

	return filepath.Join(projectDir, value)
}

func readXcodeProject(app *appleApp, xcodeproj string) {
	contents, err := os.ReadFile(filepath.Join(xcodeproj, "project.pbxproj"))
	if err != nil {
		return
	}
	app.Projects = append(app.Projects, xcodeproj)
	projectDir := filepath.Dir(xcodeproj)

	for _, block := range pbxBuildSettings.FindAllStringSubmatch(string(contents), -1) {
		settings := map[string]string{}
		for _, setting := range pbxSetting.FindAllStringSubmatch(block[1], -1) {
			settings[setting[1]] = pbxValue(setting[2])
		}
		if settings["PRODUCT_BUNDLE_IDENTIFIER"] == "" || isTestTarget(settings) {
			continue
		}
		if settings["WRAPPER_EXTENSION"] != "" && settings["WRAPPER_EXTENSION"] != "app" {
			continue
		}
		if strings.Contains(settings["INFOPLIST_FILE"], "Extension") {
			continue
		}

		if bundleID := resolveBundleID(settings["PRODUCT_BUNDLE_IDENTIFIER"], settings); bundleID != "" {
			app.BundleIDs = appendUnique(app.BundleIDs, bundleID)
		}
		if team := settings["DEVELOPMENT_TEAM"]; appleTeamID.MatchString(team) {
			app.TeamIDs = appendUnique(app.TeamIDs, team)
		}
		if path := settings["CODE_SIGN_ENTITLEMENTS"]; path != "" {
			if resolved := projectPath(projectDir, path); resolved != "" {
				app.Entitlements = appendUnique(app.Entitlements, resolved)
			}
		}
		if path := settings["INFOPLIST_FILE"]; path != "" {
			if resolved := projectPath(projectDir, path); resolved != "" {
				app.InfoPlists = appendUnique(app.InfoPlists, resolved)
			}
		}
	}
}

type expoConfig struct {
	Expo struct {
		IOS struct {
			BundleIdentifier string `json:"bundleIdentifier"`
			AppleTeamID      string `json:"appleTeamId"`
		} `json:"ios"`
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

func detectApple(root string) (appleApp, error) {
	app := appleApp{}

	err := walkProject(root, func(path string, entry fs.DirEntry) error {
		if entry.IsDir() && strings.HasSuffix(entry.Name(), ".xcodeproj") {
			readXcodeProject(&app, path)

			return filepath.SkipDir
		}

		return nil
	})
	if err != nil {
		return app, err
	}

	if len(app.Entitlements) == 0 {
		for _, project := range app.Projects {
			matches, _ := filepath.Glob(filepath.Join(filepath.Dir(project), "*", "*.entitlements"))
			for _, match := range matches {
				app.Entitlements = appendUnique(app.Entitlements, match)
			}
		}
	}

	config, path := readExpoConfig(root)
	if config.Expo.IOS.BundleIdentifier != "" {
		app.ExpoConfig = path
		app.BundleIDs = appendUnique(app.BundleIDs, config.Expo.IOS.BundleIdentifier)
		if appleTeamID.MatchString(config.Expo.IOS.AppleTeamID) {
			app.TeamIDs = appendUnique(app.TeamIDs, config.Expo.IOS.AppleTeamID)
		}
	}

	sort.Strings(app.BundleIDs)

	return app, nil
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

func readGoogleServices(app *androidApp, path string) bool {
	contents, err := os.ReadFile(path)
	if err != nil {
		return false
	}
	var services googleServices
	if json.Unmarshal(contents, &services) != nil || services.ProjectInfo.ProjectID == "" {
		return false
	}

	app.GoogleServices = path
	app.ProjectID = services.ProjectInfo.ProjectID
	app.ProjectIDSource = path
	for _, client := range services.Client {
		if name := client.ClientInfo.AndroidClientInfo.PackageName; name != "" {
			app.FirebasePackages = appendUnique(app.FirebasePackages, name)
		}
	}

	return true
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

	if app.GoogleServices == "" {
		candidates := []string{filepath.Join(module, "google-services.json")}
		flavours, _ := filepath.Glob(filepath.Join(module, "src", "*", "google-services.json"))
		candidates = append(candidates, flavours...)
		for _, candidate := range candidates {
			if readGoogleServices(app, candidate) {
				break
			}
		}
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
			if app.GoogleServices == "" {
				readGoogleServices(&app, filepath.Join(root, file))
			}
		}
	}

	if app.ProjectID == "" {
		path := filepath.Join(root, "lib", "firebase_options.dart")
		if contents, err := os.ReadFile(path); err == nil {
			if match := dartProjectID.FindSubmatch(contents); match != nil {
				app.ProjectID, app.ProjectIDSource = string(match[1]), path
			}
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
