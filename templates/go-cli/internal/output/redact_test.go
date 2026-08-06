package output

import (
	"encoding/json"
	"strings"
	"testing"
)

// Every key in SENSITIVE_KEYS (templates/cli/lib/parser.ts:54) must be caught,
// in each of the spellings the normaliser is supposed to collapse. A gap here
// is a credential leak, not a formatting bug.
func TestIsSensitiveKeyCoversEverySensitiveKey(t *testing.T) {
	for _, base := range sensitiveKeys {
		for _, spelling := range keySpellings(base) {
			if !IsSensitiveKey(spelling) {
				t.Errorf("IsSensitiveKey(%q) = false, want true (from %q)", spelling, base)
			}
		}
	}
}

// keySpellings returns the ways a sensitive key realistically appears in an API
// response or a config file.
func keySpellings(base string) []string {
	upper := strings.ToUpper(base[:1]) + base[1:]

	return []string{
		base,
		upper,
		strings.ToUpper(base),
		"x-appwrite-" + base,
		"provider" + upper,
		"provider_" + base,
		"provider-" + base,
	}
}

func TestIsSensitiveKeyIgnoresOrdinaryFields(t *testing.T) {
	// "name" and "id" must stay visible; "keyword" and "passwordless" are the
	// interesting cases -- suffix matching must not fire on a prefix match.
	insensitive := []string{
		"name", "id", "$id", "email", "endpoint", "status", "total",
		"keyword", "keyboard", "secretary", "jwtIssuer", "passwordPolicy",
	}
	for _, key := range insensitive {
		if IsSensitiveKey(key) {
			t.Errorf("IsSensitiveKey(%q) = true, want false", key)
		}
	}
}

func TestMaskString(t *testing.T) {
	cases := []struct {
		name  string
		value string
		key   string
		want  string
	}{
		{"short values masked whole", "abc123", "secret", HiddenValue},
		{"exactly 16 masked whole", "0123456789abcdef", "secret", HiddenValue},
		{"passwords always masked whole", strings.Repeat("p", 64), "password", HiddenValue},
		{"password case insensitive", strings.Repeat("p", 64), "userPassword", HiddenValue},
		{
			"prefixed credential keeps its prefix",
			"standard_0123456789abcdefghij",
			"apiKey",
			"standard_" + HiddenValue + "ghij",
		},
		{
			"unprefixed credential keeps a tail",
			"0123456789abcdefghij",
			"accessToken",
			HiddenValue + "ghij",
		},
	}

	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			if got := MaskString(tc.value, tc.key); got != tc.want {
				t.Errorf("MaskString(%q, %q) = %q, want %q", tc.value, tc.key, got, tc.want)
			}
		})
	}
}

// A leading underscore, or one too close to the end, must not produce a prefix
// split -- that would expose more of the credential than intended.
func TestMaskStringUnderscoreBounds(t *testing.T) {
	leading := MaskString("_123456789abcdefghij", "secret")
	if strings.HasPrefix(leading, "_") {
		t.Errorf("leading underscore produced a prefix split: %q", leading)
	}

	trailing := MaskString("0123456789abcdef_ghi", "secret")
	if strings.Contains(trailing, "_"+HiddenValue) {
		t.Errorf("late underscore produced a prefix split: %q", trailing)
	}
}

func TestRedactorMasksNestedStructures(t *testing.T) {
	input := map[string]any{
		"name": "my project",
		"providers": []any{
			map[string]any{"name": "smtp", "password": strings.Repeat("x", 32)},
		},
		"nested": map[string]any{"apiKey": "standard_0123456789abcdefghij"},
	}

	redactor := &Redactor{}
	masked, ok := redactor.Mask(input, "").(map[string]any)
	if !ok {
		t.Fatal("Mask did not return a map")
	}

	if !redactor.Applied {
		t.Error("Applied = false, want true")
	}
	if masked["name"] != "my project" {
		t.Errorf("non-sensitive field was altered: %v", masked["name"])
	}

	nested := masked["nested"].(map[string]any)
	if nested["apiKey"] != "standard_"+HiddenValue+"ghij" {
		t.Errorf("nested apiKey = %v", nested["apiKey"])
	}

	provider := masked["providers"].([]any)[0].(map[string]any)
	if provider["password"] != HiddenValue {
		t.Errorf("provider password = %v", provider["password"])
	}
}

func TestRedactorShowSecretsDisablesMasking(t *testing.T) {
	input := map[string]any{"secret": "standard_0123456789abcdefghij"}

	redactor := &Redactor{ShowSecrets: true}
	masked := redactor.Mask(input, "").(map[string]any)

	if masked["secret"] != input["secret"] {
		t.Errorf("secret was masked despite ShowSecrets: %v", masked["secret"])
	}
	if redactor.Applied {
		t.Error("Applied = true, want false when secrets are shown")
	}
}

// A null credential stays null rather than becoming the string "[hidden]":
// scripts distinguish "not set" from "set but hidden".
func TestRedactorPreservesNullAndNonStrings(t *testing.T) {
	redactor := &Redactor{}
	masked := redactor.Mask(map[string]any{
		"secret": nil,
		"apiKey": json.Number("42"),
	}, "").(map[string]any)

	if masked["secret"] != nil {
		t.Errorf("null secret = %v, want nil", masked["secret"])
	}
	if masked["apiKey"] != HiddenValue {
		t.Errorf("non-string credential = %v, want %q", masked["apiKey"], HiddenValue)
	}
}

// A flag name is not a response field, and the two gaps below are exactly why
// IsSensitiveKey cannot be reused for one: both of these reach a public issue
// body through --report.
func TestIsSensitiveFlagName(t *testing.T) {
	sensitive := []string{
		"key", "k", "K",
		"password", "password-signer-key",
		"secret", "jwt", "api-key", "access-token", "client-secret",
	}
	for _, name := range sensitive {
		if !IsSensitiveFlagName(name) {
			t.Errorf("IsSensitiveFlagName(%q) = false, it carries a credential", name)
		}
	}

	ordinary := []string{
		"endpoint", "project-id", "self-signed", "json", "force",
		"verbose", "report", "email", "user-id", "keys",
	}
	for _, name := range ordinary {
		if IsSensitiveFlagName(name) {
			t.Errorf("IsSensitiveFlagName(%q) = true, it is not a credential", name)
		}
	}
}

// `key` is deliberately absent from the shared list: it is an ordinary field
// name in API payloads, and masking it there would redact responses that hold
// no credential. Asserted so the two predicates cannot be collapsed by someone
// who reads only one of them.
func TestIsSensitiveKeyStillIgnoresBareKey(t *testing.T) {
	if IsSensitiveKey("key") {
		t.Error(`IsSensitiveKey("key") = true, which would mask ordinary response fields`)
	}
	if !IsSensitiveFlagName("key") {
		t.Error(`IsSensitiveFlagName("key") = false, but --key carries a credential`)
	}
}
