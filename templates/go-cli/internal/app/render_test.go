package app

import (
	"bytes"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// The captured response, not the typed struct, is what --raw and --json show.
//
// internal/sdk tests the transport and internal/output tests the renderers;
// only this covers the wiring between them, which is where the bug lived --
// both halves worked and Render never asked for the body.
func TestRenderPrefersTheCapturedResponse(t *testing.T) {
	buffer := &bytes.Buffer{}
	renderer := &output.Renderer{Mode: output.ModeRaw, Writer: buffer}

	// What the SDK's typed model would have produced: no `undeclared` field.
	structResult := map[string]any{"total": 1}

	sdk.LastResponse.Take()
	sdk.LastResponse.Record([]byte(`{"total":1,"undeclared":"kept"}`))

	if err := render(renderer, structResult); err != nil {
		t.Fatal(err)
	}

	if !strings.Contains(buffer.String(), "undeclared") {
		t.Errorf("rendered the typed struct, dropping fields:\n%s", buffer.String())
	}
}

// A command that produces a result without a request behind it still renders.
func TestRenderFallsBackToTheValue(t *testing.T) {
	buffer := &bytes.Buffer{}
	renderer := &output.Renderer{Mode: output.ModeRaw, Writer: buffer}

	sdk.LastResponse.Take()

	if err := render(renderer, map[string]any{"local": "only"}); err != nil {
		t.Fatal(err)
	}

	if !strings.Contains(buffer.String(), "local") {
		t.Errorf("nothing rendered without a captured response:\n%s", buffer.String())
	}
}

// Two renders, one response: the second must not reprint the first's body.
func TestRenderDoesNotReuseAStaleResponse(t *testing.T) {
	renderer := func(buffer *bytes.Buffer) *output.Renderer {
		return &output.Renderer{Mode: output.ModeRaw, Writer: buffer}
	}

	sdk.LastResponse.Take()
	sdk.LastResponse.Record([]byte(`{"from":"the request"}`))

	first := &bytes.Buffer{}
	if err := render(renderer(first), map[string]any{"from": "the struct"}); err != nil {
		t.Fatal(err)
	}

	second := &bytes.Buffer{}
	if err := render(renderer(second), map[string]any{"from": "the struct"}); err != nil {
		t.Fatal(err)
	}

	if strings.Contains(second.String(), "the request") {
		t.Errorf("second render reused the captured body:\n%s", second.String())
	}
}
