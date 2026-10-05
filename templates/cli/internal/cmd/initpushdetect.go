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

type appleTarget struct {
	Project      string
	BundleID     string
	TeamID       string
	Entitlements []string
	InfoPlists   []string
}

type appleApp struct {
	Projects   []string
	Targets    []appleTarget
	BundleIDs  []string
	TeamIDs    []string
	ExpoConfig string
}

func (a appleApp) found() bool {
	return len(a.Projects) > 0 || a.ExpoConfig != ""
}

func (a appleApp) targets(bundleID string) []appleTarget {
	var targets []appleTarget
	for _, target := range a.Targets {
		if target.BundleID == bundleID {
			targets = append(targets, target)
		}
	}

	return targets
}

func (a appleApp) teams(bundleID string) []string {
	var teams []string
	for _, target := range a.targets(bundleID) {
		if target.TeamID != "" {
			teams = appendUnique(teams, target.TeamID)
		}
	}
	if len(teams) == 0 && len(a.TeamIDs) == 1 {
		return a.TeamIDs
	}

	return teams
}

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

func (a androidApp) found() bool {
	return len(a.Modules) > 0 || a.ExpoConfig != ""
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

const applicationProductType = "com.apple.product-type.application"

var (
	pbxObject        = regexp.MustCompile(`(?ms)^\t\t([0-9A-Fa-f]{24})(?: /\*.*?\*/)? = \{\n(.*?)\n\t\t\};`)
	pbxIsa           = regexp.MustCompile(`(?m)^\t\t\tisa = (\w+);`)
	pbxProductType   = regexp.MustCompile(`(?m)^\t\t\tproductType = "?([^";]+)"?;`)
	pbxConfigList    = regexp.MustCompile(`(?m)^\t\t\tbuildConfigurationList = ([0-9A-Fa-f]{24})`)
	pbxConfigs       = regexp.MustCompile(`(?s)buildConfigurations = \((.*?)\);`)
	pbxReference     = regexp.MustCompile(`[0-9A-Fa-f]{24}`)
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

func projectPath(projectDir, value string) string {
	value = strings.TrimPrefix(value, "$(SRCROOT)/")
	value = strings.TrimPrefix(value, "${SRCROOT}/")
	value = strings.TrimPrefix(value, "$(PROJECT_DIR)/")
	if strings.Contains(value, "$") {
		return ""
	}

	return filepath.Join(projectDir, value)
}

func pbxSettings(body string) map[string]string {
	settings := map[string]string{}
	block := pbxBuildSettings.FindStringSubmatch(body)
	if block == nil {
		return settings
	}
	for _, setting := range pbxSetting.FindAllStringSubmatch(block[1], -1) {
		settings[setting[1]] = pbxValue(setting[2])
	}

	return settings
}

func readXcodeProject(app *appleApp, xcodeproj string) {
	contents, err := os.ReadFile(filepath.Join(xcodeproj, "project.pbxproj"))
	if err != nil {
		return
	}
	app.Projects = append(app.Projects, xcodeproj)
	projectDir := filepath.Dir(xcodeproj)

	objects := map[string]string{}
	var applications []string
	for _, object := range pbxObject.FindAllStringSubmatch(string(contents), -1) {
		objects[object[1]] = object[2]
		isa := pbxIsa.FindStringSubmatch(object[2])
		productType := pbxProductType.FindStringSubmatch(object[2])
		if isa != nil && isa[1] == "PBXNativeTarget" && productType != nil && productType[1] == applicationProductType {
			applications = append(applications, object[2])
		}
	}

	for _, application := range applications {
		list := pbxConfigList.FindStringSubmatch(application)
		if list == nil {
			continue
		}
		configs := pbxConfigs.FindStringSubmatch(objects[list[1]])
		if configs == nil {
			continue
		}

		byBundle := map[string]*appleTarget{}
		var order []string
		for _, reference := range pbxReference.FindAllString(configs[1], -1) {
			settings := pbxSettings(objects[reference])
			bundleID := resolveBundleID(settings["PRODUCT_BUNDLE_IDENTIFIER"], settings)
			if bundleID == "" {
				continue
			}
			target, ok := byBundle[bundleID]
			if !ok {
				target = &appleTarget{Project: xcodeproj, BundleID: bundleID}
				byBundle[bundleID] = target
				order = append(order, bundleID)
			}
			if team := settings["DEVELOPMENT_TEAM"]; appleTeamID.MatchString(team) && target.TeamID == "" {
				target.TeamID = team
			}
			if path := projectPath(projectDir, settings["CODE_SIGN_ENTITLEMENTS"]); settings["CODE_SIGN_ENTITLEMENTS"] != "" && path != "" {
				target.Entitlements = appendUnique(target.Entitlements, path)
			}
			if path := projectPath(projectDir, settings["INFOPLIST_FILE"]); settings["INFOPLIST_FILE"] != "" && path != "" {
				target.InfoPlists = appendUnique(target.InfoPlists, path)
			}
		}
		for _, bundleID := range order {
			app.Targets = append(app.Targets, *byBundle[bundleID])
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

	config, path := readExpoConfig(root)
	if bundleID := config.Expo.IOS.BundleIdentifier; bundleID != "" {
		app.ExpoConfig = path
		team := ""
		if appleTeamID.MatchString(config.Expo.IOS.AppleTeamID) {
			team = config.Expo.IOS.AppleTeamID
		}
		matched := false
		for index := range app.Targets {
			if app.Targets[index].BundleID == bundleID {
				matched = true
				if app.Targets[index].TeamID == "" {
					app.Targets[index].TeamID = team
				}
			}
		}
		if !matched {
			app.Targets = append(app.Targets, appleTarget{BundleID: bundleID, TeamID: team})
		}
	}

	for _, target := range app.Targets {
		app.BundleIDs = appendUnique(app.BundleIDs, target.BundleID)
		if target.TeamID != "" {
			app.TeamIDs = appendUnique(app.TeamIDs, target.TeamID)
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
