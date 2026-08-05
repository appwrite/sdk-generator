package generator

import (
	"os"
	"path/filepath"
	"strings"
)

// Distinct from typegen.DetectLanguage, which serves `appwrite types` and knows
// eight languages. This one serves `appwrite generate`, knows only TypeScript,
// and reports a confidence level -- so a project identified only by a stray
// .js file can be told apart from one with a tsconfig.json.

// Confidence is how sure a detection is.
type Confidence string

const (
	// ConfidenceHigh means a primary indicator was found.
	ConfidenceHigh Confidence = "high"
	// ConfidenceMedium means only a secondary indicator was found.
	ConfidenceMedium Confidence = "medium"
	// ConfidenceLow means only a matching file extension was found.
	ConfidenceLow Confidence = "low"
)

// Detection is what LanguageDetector found.
type Detection struct {
	Language   Language
	Confidence Confidence
	Reason     string
}

// languageProfile is the evidence that identifies one language.
type languageProfile struct {
	language   Language
	primary    []string
	secondary  []string
	extensions []string
}

var languageProfiles = []languageProfile{
	{
		language:   LanguageTypeScript,
		primary:    []string{"tsconfig.json", "package.json", "deno.json"},
		secondary:  []string{".nvmrc", "package-lock.json", "yarn.lock", "pnpm-lock.yaml", "bun.lockb"},
		extensions: []string{".ts", ".tsx", ".js", ".jsx"},
	},
}

// DetectLanguage identifies a project's language, or reports false.
func DetectLanguage(directory string) (Detection, bool) {
	for _, profile := range languageProfiles {
		if detection, ok := profile.match(directory); ok {
			return detection, true
		}
	}

	return Detection{}, false
}

func (p languageProfile) match(directory string) (Detection, bool) {
	exists := func(name string) bool {
		_, err := os.Stat(filepath.Join(directory, name))

		return err == nil
	}

	for _, indicator := range p.primary {
		if exists(indicator) {
			return Detection{p.language, ConfidenceHigh, "Found " + indicator}, true
		}
	}

	for _, indicator := range p.secondary {
		if exists(indicator) {
			return Detection{p.language, ConfidenceMedium, "Found " + indicator}, true
		}
	}

	// Only the top level is scanned, not the tree -- matching readdirSync.
	// An unreadable directory reports no match rather than failing.
	entries, err := os.ReadDir(directory)
	if err != nil {
		return Detection{}, false
	}

	for _, entry := range entries {
		for _, extension := range p.extensions {
			if strings.HasSuffix(entry.Name(), extension) {
				return Detection{
					p.language,
					ConfidenceLow,
					"Found files with extensions: " + strings.Join(p.extensions, ", "),
				}, true
			}
		}
	}

	return Detection{}, false
}

// SupportedLanguages lists the languages `generate` can target.
func SupportedLanguages() []Language {
	languages := make([]Language, 0, len(languageProfiles))
	for _, profile := range languageProfiles {
		languages = append(languages, profile.language)
	}

	return languages
}

// NewFromDetection picks a generator by inspecting the project.
func NewFromDetection(directory string) (Generator, Detection, error) {
	detection, ok := DetectLanguage(directory)
	if !ok {
		return nil, Detection{}, &UndetectedLanguageError{}
	}

	generator, err := New(detection.Language)

	return generator, detection, err
}

// UndetectedLanguageError reports a project no profile matched.
type UndetectedLanguageError struct{}

func (e *UndetectedLanguageError) Error() string {
	names := make([]string, 0, len(languageProfiles))
	for _, language := range SupportedLanguages() {
		names = append(names, string(language))
	}

	return "could not detect project language. Supported languages: " +
		strings.Join(names, ", ") +
		". Please ensure your project has the appropriate configuration files " +
		"(e.g., package.json for TypeScript)."
}
