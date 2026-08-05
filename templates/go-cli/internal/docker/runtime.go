package docker

// The runtime tables the emulation consults. Copied verbatim rather than
// derived: the image tag and the version prefix are a published contract with
// the open-runtimes images, and inferring them would break the first time a
// runtime is added with an irregular name.

// OpenRuntimesVersion is the image tag prefix every runtime image carries.
const OpenRuntimesVersion = "v5"

// RuntimeNames maps a runtime id to the display name a function sees in
// APPWRITE_FUNCTION_RUNTIME_NAME.
var RuntimeNames = map[string]string{
	"node":      "Node.js",
	"php":       "PHP",
	"ruby":      "Ruby",
	"python":    "Python",
	"python-ml": "Python (ML)",
	"deno":      "Deno",
	"dart":      "Dart",
	"dotnet":    ".NET",
	"java":      "Java",
	"swift":     "Swift",
	"kotlin":    "Kotlin",
	"bun":       "Bun",
	"go":        "Go",
}

// SystemTool describes how one runtime is built and started.
type SystemTool struct {
	// Compiled runtimes must be rebuilt on every change; interpreted ones can
	// have their sources hot-swapped into the existing build.
	Compiled bool
	// StartCommand is passed to helpers/start.sh inside the container.
	StartCommand string
	// DependencyFiles force a rebuild rather than a hot swap when touched,
	// because the build step is what installs from them.
	DependencyFiles []string
}

// SystemTools is the per-runtime build and start configuration.
var SystemTools = map[string]SystemTool{
	"node":      {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"package.json", "package-lock.json"}},
	"php":       {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"composer.json", "composer.lock"}},
	"ruby":      {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"Gemfile", "Gemfile.lock"}},
	"python":    {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"requirements.txt", "requirements.lock"}},
	"python-ml": {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"requirements.txt", "requirements.lock"}},
	"deno":      {Compiled: false, StartCommand: "bash helpers/server.sh"},
	"dart":      {Compiled: true, StartCommand: "bash helpers/server.sh"},
	"dotnet":    {Compiled: true, StartCommand: "bash helpers/server.sh"},
	"java":      {Compiled: true, StartCommand: "bash helpers/server.sh"},
	"swift":     {Compiled: true, StartCommand: "bash helpers/server.sh"},
	"kotlin":    {Compiled: true, StartCommand: "bash helpers/server.sh"},
	"bun":       {Compiled: false, StartCommand: "bash helpers/server.sh", DependencyFiles: []string{"package.json", "package-lock.json", "bun.lockb"}},
	"go":        {Compiled: true, StartCommand: "bash helpers/server.sh"},
}

// Tool returns the configuration for a runtime name, and whether it is known.
func Tool(runtimeName string) (SystemTool, bool) {
	tool, ok := SystemTools[runtimeName]

	return tool, ok
}
