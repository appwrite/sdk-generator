// Package tui is the terminal interface. It follows relay (kontrolplane/relay) and koldstart's kdst
// ui: one model, relay's palette, and loads that run as commands with a timeout, their messages
// carrying the generation they were asked for. Appwrite's resources nest the same way all the
// way down, a list whose rows open a page whose tabs list what is under it, so the pages are a
// stack of three kinds, the project's overview, a list and a resource's page, and what each kind
// of resource shows is a spec in kinds.go.
package tui

import (
	"context"
	"fmt"
	"io"
	"os"
	"strconv"
	"strings"
	"time"

	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// backend is what the interface reads and changes, an *appwrite.Client outside tests.
type backend interface {
	List(ctx context.Context, r appwrite.Ref, opts appwrite.ListOptions) (appwrite.Page, error)
	Count(ctx context.Context, r appwrite.Ref) (int, error)
	Get(ctx context.Context, r appwrite.Ref, id string) (appwrite.Doc, error)
	Delete(ctx context.Context, r appwrite.Ref, id string) error
	Execute(ctx context.Context, function string, e appwrite.Execution) (appwrite.Doc, error)
	Download(ctx context.Context, bucket, file string, w io.Writer) (int64, error)
	Ping(ctx context.Context) (time.Duration, error)
	Usage(ctx context.Context, q appwrite.UsageQuery) (map[string][]appwrite.Point, error)
	Update(ctx context.Context, r appwrite.Ref, id string, data map[string]any) (appwrite.Doc, error)
	Create(ctx context.Context, r appwrite.Ref, data map[string]any) (appwrite.Doc, error)
	// ForProject is the backend of another project of the same instance and login, as a console
	// login opens one of its organisation's.
	ForProject(id string) backend
}

// api is the backend outside tests.
type api struct{ *appwrite.Client }

func (a api) ForProject(id string) backend { return api{a.AsProject(id)} }

// binary is the name the program was run by, for the window's title.
var binary = "appwrite terminal"

// Run opens the interface on c, in the theme of --theme, until it is quit.
func Run(ctx context.Context, c *appwrite.Client, info client.Info, theme string) error {
	useTheme(theme)
	ctx, cancel := context.WithCancel(ctx)
	defer cancel()
	m := newModel(ctx, api{c}, info)
	if _, err := tea.NewProgram(m, tea.WithContext(ctx)).Run(); err != nil {
		return fmt.Errorf("running the terminal interface: %w", err)
	}
	return nil
}

// useTheme picks the palette: dark and light paint their own background, and auto starts from
// what COLORFGBG says of the terminal's, dark without it, until the terminal answers Init's query
// of its background. Asking within the program, not before it, means a terminal that does not
// answer, as some multiplexers do not, costs no wait and leaves no reply to echo.
func useTheme(theme string) {
	switch theme {
	case "dark":
		styles.Use(true)
		styles.Paint = true
	case "light":
		styles.Use(false)
		styles.Paint = true
	default:
		styles.Use(darkGround(os.Getenv("COLORFGBG")))
		styles.Paint = false
	}
}

// darkGround reports whether COLORFGBG, as rxvt, konsole and tmux set it, names a dark
// background: its last field is the colour of the background, 0 to 6 and 8 dark. Anything else,
// or nothing, is taken as dark, as most terminals are.
func darkGround(colorfgbg string) bool {
	fields := strings.Split(colorfgbg, ";")
	bg, err := strconv.Atoi(fields[len(fields)-1])
	if err != nil || bg < 0 || bg > 15 {
		return true
	}
	return bg <= 6 || bg == 8
}
