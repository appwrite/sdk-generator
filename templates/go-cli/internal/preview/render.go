// Package preview draws a site's deployment screenshot in the terminal.
//
// The TypeScript unsets TERM_PROGRAM, KITTY_WINDOW_ID and friends to force
// terminal-image's ANSI fallback: iTerm and kitty would otherwise receive a real
// image, which cannot be framed and does not survive a copied-out transcript. So
// the fallback is the shipped rendering and the only one implemented here --
// which also means no new dependency, since image/png and image/jpeg are
// standard library and half-block cells are arithmetic.
package preview

import (
	"bytes"
	"fmt"
	"image"
	"image/color"
	_ "image/jpeg" // Registered for its decoder; the API asks for PNG.
	_ "image/png"
	"math"
	"strings"
)

// The screenshot the API is asked for.
//
// The bucket is a console-project bucket, not one of the pushed project's, so
// the request has to go through a console-authenticated client.
const (
	// Bucket is SITE_SCREENSHOT_BUCKET_ID.
	Bucket = "screenshots"

	// Width and Height are SITE_SCREENSHOT_PREVIEW_WIDTH and
	// SITE_SCREENSHOT_PREVIEW_HEIGHT: the size the preview endpoint is asked
	// to resize to, which is a request for LESS data than the original, not a
	// display size. Anything above the terminal's cell grid is thrown away
	// again here, and a 480px source already has more detail than 72 columns
	// can show.
	Width  = 480
	Height = 270
)

// The cell grid the art is drawn into. The target is narrower than the maximum
// on purpose: a preview filling a 200-column terminal edge to edge is a wall,
// and the links printed under it are what the user came for.
const (
	targetColumns = 72
	maxColumns    = 80
	minColumns    = 16
	maxRows       = 22
	minRows       = 8
)

// upperHalfBlock fills the top half of its cell.
//
// One cell therefore carries two pixels: the foreground colour paints the top
// half and the background colour the bottom, which is what makes the pixel grid
// square in a cell that is roughly twice as tall as it is wide.
const upperHalfBlock = "▀"

// ansiReset closes the colours a cell row opened.
//
// One per line rather than one per cell: without it the last cell's background
// runs to the right edge of the terminal, and repeating it after every cell is
// three times the bytes for the same picture.
const ansiReset = "\x1b[0m"

// Columns is the width to draw into, for a terminal of the given width.
//
// The -4 leaves room for the frame and a
// margin; the floor is what keeps a very narrow terminal producing a small
// picture rather than a zero-width one.
func Columns(terminalColumns int) int {
	if terminalColumns <= 0 {
		terminalColumns = 80
	}

	return max(minColumns, min(terminalColumns-4, targetColumns, maxColumns))
}

// Rows is the height to draw into, for a terminal of the given height.
//
// The -10 is the summary that has to stay
// on screen with it: the status row, the two links, and the blank lines around
// them.
func Rows(terminalRows int) int {
	if terminalRows <= 0 {
		terminalRows = 24
	}

	return max(minRows, min(terminalRows-10, maxRows))
}

// Render draws an encoded image as ANSI art at most columns wide and rows tall.
//
// The aspect ratio is preserved, so the result is usually smaller than the box
// it was given in one of the two directions.
func Render(encoded []byte, columns, rows int) (string, error) {
	source, _, err := image.Decode(bytes.NewReader(encoded))
	if err != nil {
		return "", fmt.Errorf("decoding the screenshot: %w", err)
	}

	// Two pixel rows per cell row, which is what the half block buys.
	pixels := scale(source, columns, rows*2)

	return draw(pixels), nil
}

// scale box-averages an image down to fit a pixel grid.
//
// Averaging rather than nearest-neighbour sampling: a site screenshot is mostly
// text, and dropping 90% of the rows picks whichever scanline happens to land
// on a cell boundary -- which turns a paragraph into stripes that change every
// time the terminal is resized by one column.
func scale(source image.Image, width, height int) [][]color.NRGBA {
	bounds := source.Bounds()
	sourceWidth := bounds.Dx()
	sourceHeight := bounds.Dy()

	if sourceWidth <= 0 || sourceHeight <= 0 || width <= 0 || height <= 0 {
		return nil
	}

	factor := math.Min(
		float64(width)/float64(sourceWidth),
		float64(height)/float64(sourceHeight),
	)
	// Rounded DOWN, not to nearest. The dimension the factor is taken from
	// comes out exact either way, and rounding the other one up is how a 16:9
	// screenshot -- 270 rows at 0.15 is 40.5 -- gained a 41st pixel row and so
	// a 21st cell row that is half padding.
	targetWidth := max(1, int(float64(sourceWidth)*factor))
	targetHeight := max(1, int(float64(sourceHeight)*factor))

	pixels := make([][]color.NRGBA, targetHeight)
	for y := range pixels {
		pixels[y] = make([]color.NRGBA, targetWidth)
		top := bounds.Min.Y + y*sourceHeight/targetHeight
		bottom := max(top+1, bounds.Min.Y+(y+1)*sourceHeight/targetHeight)

		for x := range pixels[y] {
			left := bounds.Min.X + x*sourceWidth/targetWidth
			right := max(left+1, bounds.Min.X+(x+1)*sourceWidth/targetWidth)
			pixels[y][x] = average(source, left, top, right, bottom)
		}
	}

	return pixels
}

// white is the colour an unset pixel takes, for the reasons average gives.
var white = color.NRGBA{R: 0xff, G: 0xff, B: 0xff, A: 0xff}

// whiteRow is a row of pixels the picture does not cover.
func whiteRow(width int) []color.NRGBA {
	row := make([]color.NRGBA, width)
	for x := range row {
		row[x] = white
	}

	return row
}

// average is the mean colour of one source rectangle, over white.
//
// Over white because a screenshot with a transparent background is a page that
// never set one, and a browser renders that white. Compositing over black --
// which is what using the premultiplied values unchanged would do -- inverts
// exactly the pages that look most ordinary in a browser.
func average(source image.Image, left, top, right, bottom int) color.NRGBA {
	var red, green, blue, count uint64

	for y := top; y < bottom; y++ {
		for x := left; x < right; x++ {
			// RGBA returns alpha-premultiplied values in [0, 0xffff], so the
			// uncovered part of the pixel is what is left of the alpha.
			r, g, b, a := source.At(x, y).RGBA()
			transparent := uint64(0xffff - a)
			red += uint64(r) + transparent
			green += uint64(g) + transparent
			blue += uint64(b) + transparent
			count++
		}
	}

	if count == 0 {
		return white
	}

	return color.NRGBA{
		R: uint8(min(red/count, 0xffff) >> 8),
		G: uint8(min(green/count, 0xffff) >> 8),
		B: uint8(min(blue/count, 0xffff) >> 8),
		A: 0xff,
	}
}

// draw turns a pixel grid into half-block cells.
//
// Truecolor escapes, the same as terminal-image's ANSI fallback emits. A
// terminal limited to 256 colours degrades them itself, which is a better
// picture than quantising here would produce.
func draw(pixels [][]color.NRGBA) string {
	if len(pixels) == 0 {
		return ""
	}

	var builder strings.Builder

	for y := 0; y < len(pixels); y += 2 {
		if y > 0 {
			builder.WriteByte('\n')
		}

		// The colours currently in effect. A row of a web page is mostly one
		// colour, and re-stating it per cell was 40 bytes a cell -- 57KB for a
		// 72-column preview, all of which the terminal has to parse and the
		// scrollback has to hold. Reset at the end of each line, so the state
		// is unknown again at the start of the next one.
		var upperInEffect, lowerInEffect color.NRGBA
		fresh := true

		upper := pixels[y]
		// An odd number of pixel rows leaves the last cell half empty. It is
		// painted white rather than left to the terminal's background, so the
		// bottom edge of the picture is the picture's own colour rather than a
		// stripe of whatever theme is in use.
		lower := whiteRow(len(upper))
		if y+1 < len(pixels) {
			lower = pixels[y+1]
		}

		for x := range upper {
			if fresh || upper[x] != upperInEffect {
				fmt.Fprintf(&builder, "\x1b[38;2;%d;%d;%dm",
					upper[x].R, upper[x].G, upper[x].B)
				upperInEffect = upper[x]
			}
			if fresh || lower[x] != lowerInEffect {
				fmt.Fprintf(&builder, "\x1b[48;2;%d;%d;%dm",
					lower[x].R, lower[x].G, lower[x].B)
				lowerInEffect = lower[x]
			}
			fresh = false

			builder.WriteString(upperHalfBlock)
		}

		builder.WriteString(ansiReset)
	}

	return builder.String()
}
