package cmd

import (
	"encoding/json"
	"fmt"
	"strings"
	"testing"

	"github.com/spf13/cobra"
)

// A year of usage answers with fourteen metrics of 365 entries. Printing them
// whole was six thousand lines to say "an array of {value, date}".
func TestLongArraysAreCollapsed(t *testing.T) {
	entries := make([]string, 0, 365)
	for day := range 365 {
		entries = append(entries, fmt.Sprintf(`{"value":%d,"date":"day-%d"}`, day, day))
	}
	body := []byte(`{"requests":[` + strings.Join(entries, ",") + `]}`)

	printed := prettyJSON(body)

	if strings.Count(printed, `"value"`) != arrayPreview {
		t.Errorf("kept %d elements, want %d:\n%s",
			strings.Count(printed, `"value"`), arrayPreview, printed)
	}
	if !strings.Contains(printed, "... 363 more of 365") {
		t.Errorf("the omitted count is missing:\n%s", printed)
	}
}

// The field that fails to decode can be anywhere in the response --
// `embeddingsText` comes after fourteen other metrics -- so a line or byte cap
// would cut off exactly the part the user needs. Every KEY has to survive.
func TestEveryFieldSurvivesCollapsing(t *testing.T) {
	long := `[{"value":0},{"value":0},{"value":0},{"value":0},{"value":0}]`
	body := []byte(`{"requests":` + long + `,"network":` + long + `,"embeddingsText":` + long + `}`)

	printed := prettyJSON(body)

	for _, key := range []string{"requests", "network", "embeddingsText"} {
		if !strings.Contains(printed, `"`+key+`"`) {
			t.Errorf("%q was cut from the dump:\n%s", key, printed)
		}
	}
}

// Key order is the response's, not Go's map order: a dump printed with its
// fields shuffled is hard to compare against a model.
func TestKeyOrderIsPreserved(t *testing.T) {
	printed := prettyJSON([]byte(`{"zebra":1,"alpha":2,"middle":3}`))

	zebra := strings.Index(printed, "zebra")
	alpha := strings.Index(printed, "alpha")
	middle := strings.Index(printed, "middle")

	if !(zebra < alpha && alpha < middle) {
		t.Errorf("the fields were reordered:\n%s", printed)
	}
}

// A short array is left alone -- collapsing two entries into "0 more" would be
// noise.
func TestShortArraysAreUntouched(t *testing.T) {
	printed := prettyJSON([]byte(`{"a":[1,2]}`))

	if strings.Contains(printed, "more of") {
		t.Errorf("a short array was collapsed:\n%s", printed)
	}
	if !strings.Contains(printed, "1") || !strings.Contains(printed, "2") {
		t.Errorf("an element was dropped:\n%s", printed)
	}
}

func TestEmptyContainersRender(t *testing.T) {
	printed := prettyJSON([]byte(`{"a":[],"b":{}}`))

	if !strings.Contains(printed, "[]") || !strings.Contains(printed, "{}") {
		t.Errorf("an empty container did not survive:\n%s", printed)
	}
}

// Numbers stay as written. An id that arrived as 20 digits must not come back in
// scientific notation from the tool explaining what arrived.
func TestLargeNumbersAreNotReformatted(t *testing.T) {
	printed := prettyJSON([]byte(`{"id":12345678901234567890}`))

	if !strings.Contains(printed, "12345678901234567890") {
		t.Errorf("a large number was reformatted:\n%s", printed)
	}
}

// A body that is not JSON is the case that matters most: an HTML error page is
// the whole diagnosis, so it must be printed rather than swallowed.
func TestNonJSONBodiesArePrintedAsTheyCame(t *testing.T) {
	body := []byte("<html><body>502 Bad Gateway</body></html>")

	if printed := prettyJSON(body); printed != string(body) {
		t.Errorf("an HTML body was mangled: %q", printed)
	}
}

// Truncated mid-stream -- a connection that dropped -- still prints what arrived.
func TestATruncatedBodyStillPrints(t *testing.T) {
	printed := prettyJSON([]byte(`{"a":[1,2`))

	if !strings.Contains(printed, "a") {
		t.Errorf("a truncated body printed nothing useful: %q", printed)
	}
}

// The collapsed dump is for a person, but what is NOT collapsed must still be
// faithful -- so a response with no long arrays re-emits as equivalent JSON.
func TestAnUncollapsedDumpIsStillValidJSON(t *testing.T) {
	body := []byte(`{"a":1,"b":{"c":[true,null]},"d":"x"}`)

	var reparsed any
	if err := json.Unmarshal([]byte(prettyJSON(body)), &reparsed); err != nil {
		t.Errorf("the dump is not valid JSON: %s\n%s", err, prettyJSON(body))
	}
}

func TestErrorDetailNamesTheErrorType(t *testing.T) {
	detail := ErrorDetail(&json.UnmarshalTypeError{Value: "array", Field: "embeddingsText"})

	if !strings.Contains(detail, "UnmarshalTypeError") {
		t.Errorf("the error type is missing:\n%s", detail)
	}
}

func TestErrorDetailOfNothingIsNothing(t *testing.T) {
	if got := ErrorDetail(nil); got != "" {
		t.Errorf("ErrorDetail(nil) = %q", got)
	}
}

// cobra's own message is `accepts 1 arg(s), received 0`, which names neither the
// argument, nor the command, nor a way to find out.
func TestRequiredArgumentNamesWhatIsMissing(t *testing.T) {
	command := &cobra.Command{Use: "types"}
	validate := RequiredArgument("<output-directory>", "The directory to write the types to")

	err := validate(command, nil)
	if err == nil {
		t.Fatal("a missing argument was accepted")
	}

	message := err.Error()
	for _, want := range []string{"<output-directory>", "directory to write the types to", "types"} {
		if !strings.Contains(message, want) {
			t.Errorf("the message does not mention %q: %s", want, message)
		}
	}
	if strings.Contains(message, "arg(s)") {
		t.Errorf("cobra's plural hedge survived: %s", message)
	}
}

// Too many says how many, because the usual cause is an unquoted path with a
// space in it and the count is the clue.
func TestRequiredArgumentRejectsTooMany(t *testing.T) {
	validate := RequiredArgument("<output-directory>", "The directory")

	err := validate(&cobra.Command{Use: "types"}, []string{"a", "b"})
	if err == nil {
		t.Fatal("two arguments were accepted for a one-argument command")
	}
	if !strings.Contains(err.Error(), "given 2") {
		t.Errorf("the count is missing: %s", err)
	}
}

func TestRequiredArgumentAcceptsExactlyOne(t *testing.T) {
	validate := RequiredArgument("<output-directory>", "The directory")

	if err := validate(&cobra.Command{Use: "types"}, []string{"./types"}); err != nil {
		t.Errorf("one argument was rejected: %s", err)
	}
}

// The captured body reaches the terminal through a path that builds no
// Redactor, so --verbose printed in full what the normal render path masks.
// `project create-key --verbose` is the case that matters: the response holds a
// live secret.
func TestPrettyJSONMasksCredentials(t *testing.T) {
	const secret = "standard_9f8e7d6c5b4a39281706152433445566778899aa"

	tests := []struct {
		name string
		body string
	}{
		{name: "secret", body: `{"$id":"abc","secret":"` + secret + `"}`},
		{name: "nested under a model", body: `{"key":{"secret":"` + secret + `"}}`},
		{name: "array under a sensitive key", body: `{"secret":["` + secret + `","b"]}`},
		{name: "suffix match", body: `{"providerAccessToken":"` + secret + `"}`},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			printed := prettyJSON([]byte(test.body))

			if strings.Contains(printed, secret) {
				t.Errorf("--verbose printed the credential in full:\n%s", printed)
			}
			if !strings.Contains(printed, "hidden") {
				t.Errorf("nothing was masked:\n%s", printed)
			}
		})
	}
}

// Masking must not damage the dump: it is the diagnosis, and an unparseable one
// is worse than a redacted one.
func TestPrettyJSONStaysValidJSONWhenMasking(t *testing.T) {
	printed := prettyJSON([]byte(`{"secret":"standard_9f8e7d6c5b4a39281706152433445566","id":7}`))

	var reparsed map[string]any
	if err := json.Unmarshal([]byte(printed), &reparsed); err != nil {
		t.Fatalf("masked dump is not valid JSON: %s\n%s", err, printed)
	}
	if reparsed["id"] != float64(7) {
		t.Errorf("masking altered an unrelated field: %v", reparsed["id"])
	}
}

// Ordinary fields must survive untouched, or the dump stops being a faithful
// record of what arrived.
func TestPrettyJSONLeavesOrdinaryFieldsAlone(t *testing.T) {
	body := `{"$id":"64d1e2f3","name":"my-bucket","enabled":true}`

	if printed := prettyJSON([]byte(body)); strings.Contains(printed, "hidden") {
		t.Errorf("masked a field that holds no credential:\n%s", printed)
	}
}
