package tui

import (
	"slices"
	"strings"

	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
){% verbatim %}

// peak is the highest point of a series, 0 for none.
func peak(series []float64) float64 {
	if len(series) == 0 {
		return 0
	}
	return slices.Max(series)
}

// plainText is the text of a cell without its tones.
func plainText(c cell) string {
	var b strings.Builder
	for _, s := range c {
		b.WriteString(s.Text)
	}
	return b.String()
}

// metricSeries is a metric of the window as a sparkline draws it: its points, a bucket each, the
// tone of its blocks, what it comes to over the window, and how a point is written, with its unit
// after a range. The lowest point of a gauge, a level as storage, is of the buckets it was
// sampled in.
type metricSeries struct {
	label  string
	points []float64
	tone   styles.Tone
	// stat names what total is when it is not a sum, as p99 or max; it is drawn faint in a column
	// of its own, so the numbers of every series line up.
	stat   string
	total  cell
	format func(float64) string
	unit   string
	gauge  bool
}

// figures are the range of the series, lowest to highest, and its last point, empty for a series
// of nothing but zeros.
func (s metricSeries) figures() (string, string) {
	if peak(s.points) == 0 {
		return "", ""
	}
	low := slices.Max(s.points)
	for _, v := range s.points {
		if v > 0 || !s.gauge {
			low = min(low, v)
		}
	}
	last := s.points[len(s.points)-1]
	lastText := s.format(last)
	if s.gauge && last == 0 {
		lastText = "-"
	}
	return between(s.format(low), s.format(peak(s.points))) + s.unit, lastText
}

// between writes a range from low to high, the unit once where both have the same: 40–240ms,
// 900ms–1.2s.
func between(low, high string) string {
	unit := strings.TrimLeft(high, "0123456789.,")
	if number, ok := strings.CutSuffix(low, unit); ok && unit != "" && strings.Trim(number, "0123456789.,") == "" {
		low = number
	}
	return low + "–" + high
}

// sparkMin is the narrowest a sparkline of more points is drawn; the figures beside it go first.
const sparkMin = 12

// sparkLines draw each series as a sparkline, all the same width, what it comes to beside it,
// its range and its last point as far as they fit, each right aligned, and under them the window
// they span. A series of zeros is a dotted baseline, and one without points says so.
func sparkLines(series []metricSeries, width int) []string {
	points, statW, totalW, rangeW, lastW := 0, 0, 0, 0, 0
	ranges, lasts := make([]string, len(series)), make([]string, len(series))
	for i, s := range series {
		points = max(points, len(s.points))
		ranges[i], lasts[i] = s.figures()
		if s.stat != "" {
			statW = max(statW, len(s.stat)+1)
		}
		totalW = max(totalW, ansi.StringWidth(plainText(s.total)))
		rangeW = max(rangeW, ansi.StringWidth(ranges[i]))
		if lasts[i] != "" {
			lastW = max(lastW, len("last ")+ansi.StringWidth(lasts[i]))
		}
	}
	room, need := width-labelWidth-2-statW-totalW, min(sparkMin, points)
	switch {
	case room-2-rangeW-2-lastW >= need:
	case room-2-rangeW >= need:
		lastW = 0
	default:
		rangeW, lastW = 0, 0
	}
	if rangeW > 0 {
		room -= 2 + rangeW
	}
	if lastW > 0 {
		room -= 2 + lastW
	}
	spark := max(1, room)
	cols := []column{{width: labelWidth - 2, right: true}, {width: 2}, {width: spark}, {width: 2}, {width: statW}, {width: totalW, right: true}, {width: 2}, {width: rangeW, right: true}, {width: 2}, {width: lastW}}
	lines := make([]string, 0, len(series)+1)
	for i, s := range series {
		if len(s.points) == 0 {
			lines = append(lines, strings.TrimRight(renderCell(text(s.label, styles.ToneFaint), cols[0], nil)+pad(2, nil)+renderCell(text("no data", styles.ToneFaint), column{width: max(1, width-labelWidth)}, nil), " "))
			continue
		}
		cells := []cell{text(s.label, styles.ToneFaint), nil, sparkCell(s.points, spark, s.tone), nil, text(s.stat, styles.ToneFaint), s.total, nil, text(ranges[i], styles.ToneMuted), nil, nil}
		if peak(s.points) == 0 {
			cells[5] = text(plainText(s.total), styles.ToneFaint)
		}
		if lasts[i] != "" {
			cells[9] = cell{styles.S("last ", styles.ToneFaint), styles.S(strings.Repeat(" ", max(0, lastW-len("last ")-ansi.StringWidth(lasts[i])))+lasts[i], styles.ToneMuted)}
		}
		var b strings.Builder
		for j, cl := range cells {
			b.WriteString(renderCell(cl, cols[j], nil))
		}
		lines = append(lines, strings.TrimRight(b.String(), " "))
	}
	if axis := sparkAxis(spark, usageAgo); axis != "" {
		lines = append(lines, pad(labelWidth, nil)+styles.Faint(axis))
	}
	return lines
}

// sparkAxis is the window under a sparkline of width: how long ago it starts, as 24h, at its
// start and now at its end, shorter where that does not fit, and nothing where neither does.
func sparkAxis(width int, ago string) string {
	for _, start := range []string{ago + " ago", "-" + ago} {
		if len(start)+1+len("now") <= width {
			return start + strings.Repeat(" ", width-len(start)-len("now")) + "now"
		}
	}
	return ""
}

// sparklines are the blocks a sparkline is drawn with, from empty to full.
const sparklines = " ▁▂▃▄▅▆▇█"

// sparkline draws series in width columns, scaled to the largest point: each column the largest
// of the points it covers where there are more points than columns, and each point as wide as its
// share of the columns where there are fewer, so a sparkline spans the room it is given.
func sparkline(series []float64, width int) string {
	if len(series) == 0 || width <= 0 {
		return ""
	}
	cols := make([]float64, width)
	if width >= len(series) {
		for c := range cols {
			cols[c] = series[c*len(series)/width]
		}
	} else {
		for i, v := range series {
			c := i * width / len(series)
			cols[c] = max(cols[c], v)
		}
	}
	top := slices.Max(cols)
	blocks := []rune(sparklines)
	var b strings.Builder
	for _, v := range cols {
		i := 0
		if top > 0 && v > 0 {
			i = max(1, int(v/top*float64(len(blocks)-1)+0.5))
		}
		b.WriteRune(blocks[i])
	}
	return b.String()
}

// sparkCell is the sparkline of series in tone, its columns without a point dotted in faint, so
// a quiet stretch reads as a baseline and not as a gap.
func sparkCell(series []float64, width int, tone styles.Tone) cell {
	var c cell
	var run strings.Builder
	quiet := false
	flush := func() {
		if run.Len() == 0 {
			return
		}
		t := tone
		if quiet {
			t = styles.ToneFaint
		}
		c = append(c, styles.S(run.String(), t))
		run.Reset()
	}
	for _, r := range sparkline(series, width) {
		if (r == ' ') != quiet {
			flush()
			quiet = r == ' '
		}
		if r == ' ' {
			r = '·'
		}
		run.WriteRune(r)
	}
	flush()
	return c
}
{% endverbatim %}