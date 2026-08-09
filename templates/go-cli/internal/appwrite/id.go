package appwrite

import (
	"crypto/rand"
	"math/big"
	"strconv"
	"strings"
	"time"
)

// `init` writes `unique()` into appwrite.config.json when the user does not
// supply an id, and resolves it locally so the config is complete before
// anything is pushed.

// UniqueSentinel is what the user types to ask for a generated id.
const UniqueSentinel = "unique()"

// defaultPadding matches the SDK's default.
const defaultPadding = 7

// Unique generates an id in the SDK's format.
//
// Hex seconds, then milliseconds as five zero-padded hex digits, then random
// hex padding. The layout is reproduced exactly rather than swapped for a UUID
// because ids sort lexicographically by creation time, which is what makes a
// cursor-paginated list stable.
func Unique() string {
	return uniqueAt(time.Now(), defaultPadding, randomHexDigit)
}

// uniqueAt is Unique with the clock and randomness injected, for tests.
func uniqueAt(now time.Time, padding int, digit func() byte) string {
	seconds := now.Unix()
	milliseconds := now.Nanosecond() / int(time.Millisecond)

	var id strings.Builder
	id.WriteString(strconv.FormatInt(seconds, 16))

	// padStart(5, '0'): a millisecond value never exceeds 3e7 in hex, so five
	// digits is always enough and always the same width -- which is what keeps
	// the ids sortable.
	millisecondsHex := strconv.FormatInt(int64(milliseconds), 16)
	id.WriteString(strings.Repeat("0", max(0, 5-len(millisecondsHex))))
	id.WriteString(millisecondsHex)

	for range padding {
		id.WriteByte(digit())
	}

	return id.String()
}

// randomHexDigit returns one random hex character.
//
// crypto/rand rather than math/rand: these ids name resources in a shared
// project, and a predictable suffix invites a collision from a concurrent
// `init` on another machine.
func randomHexDigit() byte {
	const alphabet = "0123456789abcdef"

	index, err := rand.Int(rand.Reader, big.NewInt(int64(len(alphabet))))
	if err != nil {
		// A failing system RNG is not something a CLI can recover from
		// meaningfully; falling back to the low bits of the clock at least
		// keeps the id well-formed.
		return alphabet[time.Now().UnixNano()&0xf]
	}

	return alphabet[index.Int64()]
}

// ResolveID returns a concrete id, generating one for the unique() sentinel.
func ResolveID(value string) string {
	if value == UniqueSentinel {
		return Unique()
	}

	return value
}
