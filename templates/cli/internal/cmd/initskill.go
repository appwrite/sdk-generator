//go:build !browser

package cmd

import (
	"errors"
	"fmt"
	"os"
	"path/filepath"
	"regexp"
	"runtime"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Initializes skills by fetching and placing the selected definitions.

// skillsRepo holds the agent skills.
const skillsRepo = "https://github.com/appwrite/skills"

// skillInfo is one installable skill.
type skillInfo struct {
	Name        string
	Description string
	DirName     string
}

// frontmatterBlock matches the leading `---` block of a SKILL.md.
var frontmatterBlock = regexp.MustCompile(`(?s)\A---\s*\n(.*?)\n---`)

var (
	frontmatterName        = regexp.MustCompile(`(?m)^name:\s*(.+)$`)
	frontmatterDescription = regexp.MustCompile(`(?m)^description:\s*(.+)$`)
)

type initSkillOptions struct {
	all    bool
	skills []string
	agents []string
	method string
}

// parseSkillFrontmatter reads the name and description out of a SKILL.md.
func parseSkillFrontmatter(contents string) (string, string) {
	block := frontmatterBlock.FindStringSubmatch(contents)
	if block == nil {
		return "", ""
	}

	name := ""
	if match := frontmatterName.FindStringSubmatch(block[1]); match != nil {
		name = strings.TrimSpace(match[1])
	}

	description := ""
	if match := frontmatterDescription.FindStringSubmatch(block[1]); match != nil {
		description = strings.TrimSpace(match[1])
	}

	return name, description
}

func newInitSkillCommand() *cobra.Command {
	options := initSkillOptions{}
	command := &cobra.Command{
		Use:     "skill",
		Aliases: []string{"skills"},
		Short:   "Install Appwrite skills for AI coding agents",
		RunE: func(command *cobra.Command, args []string) error {
			return runInitSkill(command, options)
		},
	}

	flags := command.Flags()
	flags.BoolVar(&options.all, "all", false, "Install every available Appwrite skill")
	flags.StringArrayVar(&options.skills, "skill", nil,
		"Skill directory name to install. Repeat for multiple skills.")
	flags.StringArrayVar(&options.agents, "agent", nil,
		"Agent directory to install to: .agents or .claude. Repeat for both. Defaults to .agents for non-interactive installs.")
	flags.StringVar(&options.method, "method", "",
		"Installation method: symlink or copy. Defaults to symlink for non-interactive installs.")

	return command
}

func runInitSkill(command *cobra.Command, requested initSkillOptions) error {
	context, err := newInitContext()
	if err != nil {
		return err
	}

	out := command.OutOrStdout()
	// Skills install beside the config file, not in the working directory.
	root := context.local.Dirname()

	output.Log(out, "Fetching available Appwrite skills ...")

	skills, tempDir, err := fetchAvailableSkills()
	if err != nil {
		return err
	}
	defer os.RemoveAll(tempDir)

	options := make([]prompt.Option, 0, len(skills))
	for _, skill := range skills {
		options = append(options, prompt.Option{Label: skill.Name, Value: skill.DirName})
	}

	installAll := requested.all || app.Flags().All
	explicitSelection := installAll || len(requested.skills) > 0
	selected, err := resolveSkillSelection(skills, requested.skills, installAll)
	if err != nil {
		return err
	}
	if !explicitSelection {
		selected, err = context.prompter.MultiChoice(prompt.MultiChoice{
			Message:  "Which skills would you like to install?",
			Options:  options,
			Flag:     "--all or --skill",
			Validate: prompt.RequiredSelection("skill"),
		})
		if err != nil {
			return err
		}
	}

	agents, err := resolveSkillAgents(requested.agents)
	if err != nil {
		return err
	}
	if len(agents) == 0 {
		if explicitSelection {
			agents = []string{".agents"}
		} else {
			agents, err = context.prompter.MultiChoice(prompt.MultiChoice{
				Message:  "Which agent directories would you like to install to?",
				Options:  prompt.Options(".agents", ".claude"),
				Default:  []string{".agents"},
				Flag:     "--agent",
				Validate: prompt.RequiredSelection("agent directory"),
			})
			if err != nil {
				return err
			}
		}
	}

	method, err := resolveSkillMethod(requested.method)
	if err != nil {
		return err
	}
	if method == "" {
		if explicitSelection {
			method = "symlink"
		} else {
			method, err = context.prompter.Choice(prompt.Choice{
				Message: "How would you like to install the skills?",
				Options: []prompt.Option{
					{
						Label: "Symlink (recommended) — single source of truth, easy to update",
						Value: "symlink",
					},
					{
						Label: "Copy — independent copies in each agent directory",
						Value: "copy",
					},
				},
				Flag: "--method",
			})
			if err != nil {
				return err
			}
		}
	}

	if err := placeSkills(root, tempDir, selected, agents, method == "symlink"); err != nil {
		return err
	}

	plural := "s"
	if len(selected) == 1 {
		plural = ""
	}
	output.Success(out, "%d skill%s installed successfully.", len(selected), plural)
	output.Hint(out, "Agent skills are automatically discovered by AI coding "+
		"agents like Claude Code, Cursor, and GitHub Copilot.")

	return nil
}

func resolveSkillSelection(available []skillInfo, requested []string, all bool) ([]string, error) {
	if all && len(requested) > 0 {
		return nil, errors.New("the --all and --skill flags cannot be used together")
	}

	availableNames := make([]string, 0, len(available))
	availableSet := make(map[string]struct{}, len(available))
	for _, skill := range available {
		availableNames = append(availableNames, skill.DirName)
		availableSet[skill.DirName] = struct{}{}
	}

	if all {
		return availableNames, nil
	}

	selected := make([]string, 0, len(requested))
	seen := make(map[string]struct{}, len(requested))
	for _, name := range requested {
		if _, ok := availableSet[name]; !ok {
			return nil, fmt.Errorf("unknown skill %q. Available skills: %s",
				name, strings.Join(availableNames, ", "))
		}
		if _, ok := seen[name]; ok {
			continue
		}
		seen[name] = struct{}{}
		selected = append(selected, name)
	}

	return selected, nil
}

func resolveSkillAgents(requested []string) ([]string, error) {
	selected := make([]string, 0, len(requested))
	seen := make(map[string]struct{}, len(requested))
	for _, agent := range requested {
		if agent != ".agents" && agent != ".claude" {
			return nil, fmt.Errorf("unknown agent directory %q. Available directories: .agents, .claude", agent)
		}
		if _, ok := seen[agent]; ok {
			continue
		}
		seen[agent] = struct{}{}
		selected = append(selected, agent)
	}

	return selected, nil
}

func resolveSkillMethod(requested string) (string, error) {
	if requested != "" && requested != "symlink" && requested != "copy" {
		return "", fmt.Errorf("unknown installation method %q. Available methods: symlink, copy", requested)
	}

	return requested, nil
}

// fetchAvailableSkills clones the skills repository and reads the catalogue.
//
// The caller owns the returned directory and must remove it.
func fetchAvailableSkills() ([]skillInfo, string, error) {
	tempDir, err := os.MkdirTemp("", "appwrite-skills-")
	if err != nil {
		return nil, "", err
	}

	fail := func(err error) ([]skillInfo, string, error) {
		os.RemoveAll(tempDir)

		return nil, "", err
	}

	if err := runGit(tempDir, fmt.Sprintf(
		"git clone --single-branch --depth 1 --sparse %s .", skillsRepo)); err != nil {
		return fail(err)
	}
	if err := runGit(tempDir, "git sparse-checkout add skills"); err != nil {
		return fail(err)
	}

	skillsSrcDir := filepath.Join(tempDir, "skills")
	entries, err := os.ReadDir(skillsSrcDir)
	if err != nil {
		return fail(errors.New("No skills directory found in the repository."))
	}

	var skills []skillInfo
	for _, entry := range entries {
		if !entry.IsDir() {
			continue
		}

		contents, err := os.ReadFile(filepath.Join(skillsSrcDir, entry.Name(), "SKILL.md"))
		if err != nil {
			// A directory without a SKILL.md is not a skill.
			continue
		}

		name, description := parseSkillFrontmatter(string(contents))
		if name == "" {
			name = entry.Name()
		}

		skills = append(skills, skillInfo{
			Name:        name,
			Description: description,
			DirName:     entry.Name(),
		})
	}

	if len(skills) == 0 {
		return fail(errors.New("No skills found in the repository."))
	}

	return skills, tempDir, nil
}

// placeSkills installs the chosen skills into the chosen agent directories.
//
// Symlinking only happens with more than one agent directory: with a single one
// there is nothing to point at, so the "symlink" answer copies. The alternative
// -- a lone directory of links into a temporary clone -- breaks the moment the
// clone is removed.
func placeSkills(root, tempDir string, selected, agents []string, useSymlinks bool) error {
	skillsSrcDir := filepath.Join(tempDir, "skills")

	if !useSymlinks || len(agents) <= 1 {
		for _, agent := range agents {
			target := filepath.Join(root, agent, "skills")
			if err := os.MkdirAll(target, 0o755); err != nil {
				return err
			}

			for _, dirName := range selected {
				destination := filepath.Join(target, dirName)
				if err := os.RemoveAll(destination); err != nil {
					return err
				}
				if err := copyTree(filepath.Join(skillsSrcDir, dirName),
					destination); err != nil {
					return err
				}
			}
		}

		return nil
	}

	// The first chosen directory holds the real files; the rest link to it.
	canonical := filepath.Join(root, agents[0], "skills")
	if err := os.MkdirAll(canonical, 0o755); err != nil {
		return err
	}

	for _, dirName := range selected {
		destination := filepath.Join(canonical, dirName)
		if err := os.RemoveAll(destination); err != nil {
			return err
		}
		if err := copyTree(filepath.Join(skillsSrcDir, dirName), destination); err != nil {
			return err
		}
	}

	for _, agent := range agents[1:] {
		target := filepath.Join(root, agent, "skills")
		if err := os.MkdirAll(target, 0o755); err != nil {
			return err
		}

		for _, dirName := range selected {
			destination := filepath.Join(target, dirName)
			if err := os.RemoveAll(destination); err != nil {
				return err
			}

			// A relative link, so moving or sharing the project keeps it
			// resolvable.
			relative, err := filepath.Rel(target, filepath.Join(canonical, dirName))
			if err != nil {
				return err
			}

			if err := os.Symlink(relative, destination); err != nil {
				if runtime.GOOS == "windows" {
					return errors.New(
						"Symlinks require Developer Mode or Administrator rights on Windows.\n" +
							"Enable Developer Mode in Settings > System > For developers, " +
							"or re-run as Administrator.\n" +
							"Alternatively, use 'Copy' install mode instead.")
				}

				return err
			}
		}
	}

	return nil
}
