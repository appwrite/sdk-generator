package preview

import (
	"bytes"
	"image"
	"image/color"
	"image/png"
	"strings"
	"testing"
	"unicode/utf8"
)

// encode is a PNG of a function of x and y, for the tests to render back.
func encode(t *testing.T, width, height int, at func(x, y int) color.NRGBA) []byte {
	t.Helper()

	canvas := image.NewNRGBA(image.Rect(0, 0, width, height))
	for y := range height {
		for x := range width {
			canvas.SetNRGBA(x, y, at(x, y))
		}
	}

	var buffer bytes.Buffer
	if err := png.Encode(&buffer, canvas); err != nil {
		t.Fatalf("encoding the fixture: %s", err)
	}

	return buffer.Bytes()
}

func solid(shade color.NRGBA) func(x, y int) color.NRGBA {
	return func(_, _ int) color.NRGBA { return shade }
}

// A 16:9 screenshot -- which is what the preview endpoint is asked for -- must
// come back at the width it was given, not the height: 270 rows scaled by
// 72/480 is 40 pixel rows, so 20 cell rows, inside the 22-row bound.
func TestRenderFillsTheWidthOfAWideScreenshot(t *testing.T) {
	art, err := Render(encode(t, 480, 270, solid(color.NRGBA{A: 0xff})), 72, 22)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	lines := strings.Split(art, "\n")
	if got := utf8.RuneCountInString(visible(lines[0])); got != 72 {
		t.Errorf("rendered %d columns wide, want 72", got)
	}
	if len(lines) != 20 {
		t.Errorf("rendered %d rows, want 20", len(lines))
	}
}

// The bounding box is a bound, not a shape to stretch into: a square image in a
// 72x22 box is limited by the rows, and stretching it to 72 columns would make
// every screenshot of a phone-shaped page look like a letterbox.
func TestRenderPreservesTheAspectRatio(t *testing.T) {
	art, err := Render(encode(t, 100, 100, solid(color.NRGBA{A: 0xff})), 72, 22)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	lines := strings.Split(art, "\n")
	columns := utf8.RuneCountInString(visible(lines[0]))

	// 22 rows is 44 pixel rows, so a square image is 44x44: 44 columns and 22
	// rows of cells.
	if columns != 44 || len(lines) != 22 {
		t.Errorf("rendered %dx%d cells, want 44x22", columns, len(lines))
	}
}

// The colour a cell reports is the colour of the pixels it covers. A picture
// that renders as the wrong colour is the one failure a screenshot preview
// cannot survive, and it is invisible in a test that only measures the grid.
func TestRenderCarriesTheColourThrough(t *testing.T) {
	orange := color.NRGBA{R: 0xfd, G: 0x36, B: 0x6e, A: 0xff}
	art, err := Render(encode(t, 40, 40, solid(orange)), 20, 10)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	want := "\x1b[38;2;253;54;110m\x1b[48;2;253;54;110m" + upperHalfBlock
	if !strings.HasPrefix(art, want) {
		t.Errorf("the first cell is not the image's colour:\n%q", art[:min(len(art), 80)])
	}
}

// A page that never set a background is white in a browser. Compositing over
// black instead -- which is what the premultiplied values do untouched --
// inverts exactly the pages that look most ordinary.
func TestRenderCompositesTransparencyOverWhite(t *testing.T) {
	art, err := Render(encode(t, 4, 4, solid(color.NRGBA{})), 4, 2)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	if !strings.HasPrefix(art, "\x1b[38;2;255;255;255m") {
		t.Errorf("a transparent pixel did not render white:\n%q", art[:min(len(art), 40)])
	}
}

// Downscaling by averaging, not by sampling: a checkerboard is grey at a
// fraction of its size, and any single row of it is stripes.
func TestRenderAveragesRatherThanSamples(t *testing.T) {
	checkerboard := func(x, y int) color.NRGBA {
		if (x+y)%2 == 0 {
			return color.NRGBA{A: 0xff}
		}

		return white
	}

	art, err := Render(encode(t, 64, 64, checkerboard), 8, 4)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	// 8x8 source pixels per rendered pixel, half of them black: 127 or 128.
	if !strings.Contains(art, "\x1b[38;2;127;127;127m") &&
		!strings.Contains(art, "\x1b[38;2;128;128;128m") {
		t.Errorf("a checkerboard did not average to grey:\n%q", art[:min(len(art), 120)])
	}
}

// An odd pixel height leaves a half-filled cell rather than dropping the row,
// because dropping it is a visible slice off the bottom of the page.
func TestRenderKeepsAnOddFinalRow(t *testing.T) {
	// 3 columns by 9 rows fits its height: 9 pixel rows is 5 cell rows.
	art, err := Render(encode(t, 3, 9, solid(color.NRGBA{A: 0xff})), 3, 5)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	lines := strings.Split(art, "\n")
	if len(lines) != 5 {
		t.Fatalf("rendered %d rows, want 5", len(lines))
	}

	// The last row's lower half is the padding colour, its upper half the
	// image's.
	if !strings.Contains(lines[4], "\x1b[48;2;255;255;255m") {
		t.Errorf("the half-filled final row is not padded white:\n%q", lines[4])
	}
}

// A run of one colour states it once. Re-stating it per cell was 40 bytes a
// cell -- 57KB for one 72-column preview, all of which the terminal parses and
// the scrollback holds.
func TestRenderRestatesAColourOnlyWhenItChanges(t *testing.T) {
	art, err := Render(encode(t, 40, 40, solid(color.NRGBA{A: 0xff})), 20, 10)
	if err != nil {
		t.Fatalf("Render: %s", err)
	}

	// One foreground and one background escape per line, and nothing else,
	// because every cell of a solid image is the same colour.
	if got := strings.Count(art, "\x1b[38;2;"); got != len(strings.Split(art, "\n")) {
		t.Errorf("emitted %d foreground escapes for %d lines",
			got, len(strings.Split(art, "\n")))
	}
}

func TestRenderRejectsSomethingThatIsNotAnImage(t *testing.T) {
	if _, err := Render([]byte("<html>404</html>"), 72, 22); err == nil {
		t.Error("Render accepted a body that is not an image")
	}
}

func TestColumns(t *testing.T) {
	cases := map[int]int{
		0:   72, // Unknown width falls back to 80 columns, so the target.
		200: 72, // Wide terminal: the target, not the width.
		60:  56, // Narrow terminal: its width, less the frame and margin.
		10:  16, // Narrower than the floor: the floor.
	}

	for terminal, want := range cases {
		if got := Columns(terminal); got != want {
			t.Errorf("Columns(%d) = %d, want %d", terminal, got, want)
		}
	}
}

func TestRows(t *testing.T) {
	cases := map[int]int{
		0:  14, // Unknown height falls back to 24 rows, less the summary.
		60: 22, // Tall terminal: the bound.
		24: 14,
		12: 8, // Short terminal: the floor, so there is still a picture.
	}

	for terminal, want := range cases {
		if got := Rows(terminal); got != want {
			t.Errorf("Rows(%d) = %d, want %d", terminal, got, want)
		}
	}
}
