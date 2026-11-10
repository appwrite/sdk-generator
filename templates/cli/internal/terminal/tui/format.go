package tui

import (
	"cmp"
	"fmt"
	"strconv"
	"time"
)

// formatInt writes n in groups of three, as 12,345 and -1,024.
func formatInt(n int) string {
	s := strconv.Itoa(n)
	sign, digits := "", s
	if n < 0 {
		sign, digits = "-", s[1:]
	}
	if len(digits) <= 3 {
		return s
	}
	b := []byte(sign)
	for i, c := range []byte(digits) {
		if i > 0 && (len(digits)-i)%3 == 0 {
			b = append(b, ',')
		}
		b = append(b, c)
	}
	return string(b)
}

func orDash(s string) string {
	if s == "" {
		return "-"
	}
	return s
}

func cmpOr(values ...string) string { return cmp.Or(values...) }

// formatAgo says how long ago t was, in the largest unit that fits.
func formatAgo(t time.Time) string {
	if t.IsZero() {
		return "-"
	}
	d := time.Since(t)
	switch {
	case d < 5*time.Second:
		return "just now"
	case d < time.Minute:
		return fmt.Sprintf("%ds ago", int(d.Seconds()))
	case d < time.Hour:
		return fmt.Sprintf("%dm ago", int(d.Minutes()))
	case d < 48*time.Hour:
		return fmt.Sprintf("%dh ago", int(d.Hours()))
	default:
		return fmt.Sprintf("%dd ago", int(d.Hours()/24))
	}
}

// formatTime writes t in local time, without the year when it is this year.
func formatTime(t time.Time) string {
	if t.IsZero() {
		return "-"
	}
	t = t.Local()
	if t.Year() == time.Now().Year() {
		return t.Format("Jan 02 15:04:05")
	}
	return t.Format("2006-01-02 15:04:05")
}

// formatBytes writes a size the way a person reads it: 512 B, 3.4 kB, 12 MB.
func formatBytes(n int64) string {
	const unit = 1000
	if n < unit {
		return strconv.FormatInt(n, 10) + " B"
	}
	div, exp := int64(unit), 0
	for m := n / unit; m >= unit && exp < 4; m /= unit {
		div *= unit
		exp++
	}
	return fmt.Sprintf("%.1f %cB", float64(n)/float64(div), "kMGTP"[exp])
}

// formatSeconds writes a duration the API gives in seconds: 3ms, 340ms, 1.2s, 2m05s.
func formatSeconds(s float64) string {
	d := time.Duration(s * float64(time.Second))
	switch {
	case d <= 0:
		return "-"
	case d < time.Millisecond:
		return "<1ms"
	case d < time.Second:
		return strconv.FormatInt(d.Milliseconds(), 10) + "ms"
	case d < time.Minute:
		return strconv.FormatFloat(d.Seconds(), 'f', 1, 64) + "s"
	default:
		return fmt.Sprintf("%dm%02ds", int(d.Minutes()), int(d.Seconds())%60)
	}
}

// formatRTT writes a round trip.
func formatRTT(d time.Duration) string {
	if d <= 0 {
		return "-"
	}
	return formatSeconds(d.Seconds())
}
