package tui

import (
	"strings"
	"testing"

	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
)

// BenchmarkLargeResponse draws the response tab of an execution that answered 2 mb of json, each
// frame well under the 16ms of a frame at 60 a second, as its lines are drawn once.
func BenchmarkLargeResponse(b *testing.B) {
	var body strings.Builder
	body.WriteString("[")
	for i := range 20000 {
		if i > 0 {
			body.WriteString(",")
		}
		body.WriteString(`{"id":` + strings.Repeat("1", 5) + `,"name":"customer name here","paid":true,"total":12.5}`)
	}
	body.WriteString("]")
	raw := `{"$id":"e1","status":"completed","responseBody":` + quote(body.String()) + `}`
	f := newFake()
	f.lists["executions/hello"] = []appwrite.Doc{appwrite.MustDoc(raw)}
	m := started(b, f)
	m, _ = update(b, m, tea.WindowSizeMsg{Width: 200, Height: 60})
	m = pressKeys(b, m, "down", "enter", "enter", "enter")
	b.ResetTimer()
	for range b.N {
		_ = m.render()
	}
}

// quote writes s as a json string.
func quote(s string) string {
	return `"` + strings.ReplaceAll(strings.ReplaceAll(s, `\`, `\\`), `"`, `\"`) + `"`
}
