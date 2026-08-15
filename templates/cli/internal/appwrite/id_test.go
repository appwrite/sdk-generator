package appwrite

import (
	"regexp"
	"sort"
	"testing"
	"time"
)

// TestUniqueMatchesTheSDKLayout pins the format: hex seconds, then exactly five
// hex digits of milliseconds, then the padding.
func TestUniqueMatchesTheSDKLayout(t *testing.T) {
	moment := time.Unix(1700000000, 123*int64(time.Millisecond))

	id := uniqueAt(moment, 7, func() byte { return 'a' })

	// 1700000000 -> 6553f100, 123 -> 7b padded to 0007b, then seven 'a'.
	want := "6553f10" + "0" + "0007b" + "aaaaaaa"
	if id != want {
		t.Errorf("uniqueAt = %q, want %q", id, want)
	}
}

// TestUniqueIsSortableByCreationTime is the reason the layout is reproduced
// rather than swapped for a UUID: cursor-paginated lists rely on ids ordering
// lexicographically by creation time, which the fixed-width millisecond field
// is what guarantees.
func TestUniqueIsSortableByCreationTime(t *testing.T) {
	base := time.Unix(1700000000, 0)

	moments := []time.Time{
		base,
		base.Add(time.Millisecond),
		base.Add(999 * time.Millisecond),
		base.Add(time.Second),
		base.Add(time.Minute),
	}

	ids := make([]string, 0, len(moments))
	for _, moment := range moments {
		ids = append(ids, uniqueAt(moment, 0, func() byte { return '0' }))
	}

	sorted := append([]string(nil), ids...)
	sort.Strings(sorted)

	for index := range ids {
		if ids[index] != sorted[index] {
			t.Fatalf("ids do not sort by time:\n  chronological %v\n  sorted        %v", ids, sorted)
		}
	}
}

func TestUniqueShape(t *testing.T) {
	id := Unique()

	// Hex only, and long enough to carry both halves plus the padding.
	if !regexp.MustCompile(`^[0-9a-f]+$`).MatchString(id) {
		t.Errorf("Unique() = %q, want lowercase hex", id)
	}
	if len(id) < 13 {
		t.Errorf("Unique() = %q, want at least 13 characters", id)
	}
}

func TestUniqueDoesNotRepeat(t *testing.T) {
	seen := map[string]bool{}
	for range 200 {
		id := Unique()
		if seen[id] {
			t.Fatalf("Unique() returned %q twice", id)
		}
		seen[id] = true
	}
}

func TestResolveIDOnlyReplacesTheSentinel(t *testing.T) {
	if got := ResolveID("my-bucket"); got != "my-bucket" {
		t.Errorf("ResolveID(%q) = %q, want it unchanged", "my-bucket", got)
	}

	generated := ResolveID(UniqueSentinel)
	if generated == UniqueSentinel || generated == "" {
		t.Errorf("ResolveID(%q) = %q, want a generated id", UniqueSentinel, generated)
	}

	// An empty id is the user's choice, not a request to generate one -- the
	// API rejects it, and inventing an id would hide the mistake.
	if got := ResolveID(""); got != "" {
		t.Errorf("ResolveID(\"\") = %q, want it left empty", got)
	}
}
