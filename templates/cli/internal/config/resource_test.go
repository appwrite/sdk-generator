package config

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
)

// Invariant 2: appwrite.config.json lives in the user's repository and is
// code-reviewed. Adding a resource must change the array it touches and
// nothing else — not key order, not formatting, not an unrelated value.

func loadFrom(t *testing.T, contents string) *Local {
	t.Helper()

	path := filepath.Join(t.TempDir(), LocalFileName)
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}

	return local
}

func writtenConfig(t *testing.T, local *Local) string {
	t.Helper()

	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(local.Path())
	if err != nil {
		t.Fatal(err)
	}

	return string(contents)
}

const richConfig = `{
    "projectId": "my-project",
    "projectName": "My Project",
    "endpoint": "https://cloud.appwrite.io/v1?a=1&b=2",
    "buckets": [
        {
            "$id": "existing",
            "name": "Existing <bucket>"
        }
    ],
    "somethingUnknown": {
        "keep": "me"
    }
}`

// TestAddBucketLeavesEverythingElseAlone is the invariant test. An endpoint
// with `&`, a name with angle brackets and an unknown top-level key are all
// present precisely because each is a way a naive re-encode corrupts the file.
func TestAddBucketLeavesEverythingElseAlone(t *testing.T) {
	local := loadFrom(t, richConfig)

	if err := local.AddBucket(Bucket{ID: "added", Name: "Added"}); err != nil {
		t.Fatal(err)
	}

	written := writtenConfig(t, local)

	for _, fragment := range []string{
		`"endpoint": "https://cloud.appwrite.io/v1?a=1&b=2"`,
		`"name": "Existing <bucket>"`,
		`"keep": "me"`,
		`"$id": "added"`,
	} {
		if !strings.Contains(written, fragment) {
			t.Errorf("written config lost %s:\n%s", fragment, written)
		}
	}

	// HTML escaping is the specific corruption jsonx exists to prevent.
	if strings.Contains(written, `\u0026`) || strings.Contains(written, `\u003c`) {
		t.Errorf("written config is HTML-escaped:\n%s", written)
	}

	// projectId must still precede endpoint: the canonical key order is what
	// keeps a config from churning in review.
	if strings.Index(written, `"projectId"`) > strings.Index(written, `"endpoint"`) {
		t.Errorf("key order changed:\n%s", written)
	}
}

// TestAddBucketReplacesRatherThanDuplicates pins re-running init: the same id
// updates in place, so the config does not grow a second entry.
func TestAddBucketReplacesRatherThanDuplicates(t *testing.T) {
	local := loadFrom(t, richConfig)

	if err := local.AddBucket(Bucket{ID: "existing", Name: "Renamed"}); err != nil {
		t.Fatal(err)
	}

	written := writtenConfig(t, local)

	if strings.Count(written, `"$id": "existing"`) != 1 {
		t.Errorf("bucket was duplicated rather than replaced:\n%s", written)
	}
	if !strings.Contains(written, `"name": "Renamed"`) {
		t.Errorf("bucket was not updated:\n%s", written)
	}
	if strings.Contains(written, "Existing <bucket>") {
		t.Errorf("the old value survived:\n%s", written)
	}
}

// TestAddCollectionKeyedOnDatabaseToo pins the composite identity. Two
// databases may hold a collection with the same id, and matching on id alone
// would overwrite the wrong one.
func TestAddCollectionKeyedOnDatabaseToo(t *testing.T) {
	local := loadFrom(t, `{"projectId": "p"}`)

	for _, database := range []string{"one", "two"} {
		if err := local.AddCollection(Collection{
			ID: "shared", DatabaseID: database, Name: "In " + database,
		}); err != nil {
			t.Fatal(err)
		}
	}

	written := writtenConfig(t, local)

	if strings.Count(written, `"$id": "shared"`) != 2 {
		t.Errorf("the second database's collection replaced the first:\n%s", written)
	}

	// And a repeat of the same pair still replaces.
	if err := local.AddCollection(Collection{
		ID: "shared", DatabaseID: "one", Name: "Updated",
	}); err != nil {
		t.Fatal(err)
	}

	written = writtenConfig(t, local)
	if strings.Count(written, `"$id": "shared"`) != 2 {
		t.Errorf("re-adding the same pair duplicated it:\n%s", written)
	}
	if !strings.Contains(written, `"name": "Updated"`) {
		t.Errorf("the matching entry was not updated:\n%s", written)
	}
}

// TestAddToAbsentArrayCreatesIt covers the first resource of its kind.
func TestAddToAbsentArrayCreatesIt(t *testing.T) {
	local := loadFrom(t, `{"projectId": "p"}`)

	if err := local.AddTeam(Team{ID: "t", Name: "Team"}); err != nil {
		t.Fatal(err)
	}

	written := writtenConfig(t, local)
	if !strings.Contains(written, `"teams"`) {
		t.Errorf("teams array was not created:\n%s", written)
	}
}

// TestOmittedFieldsAreNotWritten pins the whitelist behaviour a typed struct
// gives for free: an API response does not drag $createdAt into the config.
func TestOmittedFieldsAreNotWritten(t *testing.T) {
	local := loadFrom(t, `{"projectId": "p"}`)

	if err := local.AddTeam(Team{ID: "t", Name: "Team"}); err != nil {
		t.Fatal(err)
	}

	written := writtenConfig(t, local)
	if strings.Contains(written, "createdAt") || strings.Contains(written, "null") {
		t.Errorf("unexpected keys were written:\n%s", written)
	}
}

// TestEmptySlicesSurviveAsEmptyArrays pins the distinction `init collection`
// depends on: `"attributes": []` means "declared with none", while a missing
// key reads as "not yet pulled".
func TestEmptySlicesSurviveAsEmptyArrays(t *testing.T) {
	local := loadFrom(t, `{"projectId": "p"}`)

	if err := local.AddCollection(Collection{
		ID: "c", DatabaseID: "d", Name: "C",
		Attributes: []any{}, Indexes: []any{},
	}); err != nil {
		t.Fatal(err)
	}

	written := writtenConfig(t, local)
	if !strings.Contains(written, `"attributes": []`) {
		t.Errorf("empty attributes array was dropped:\n%s", written)
	}
}
