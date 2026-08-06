package output

import (
	"encoding/json"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// A regression here leaks credentials into terminal scrollback, CI logs and
// bug reports. The rules are reproduced exactly, including the parts that look
// arbitrary.

// HiddenValue replaces a redacted value.
const HiddenValue = "[hidden]"

// sensitiveKeys is matched against a normalised key. Ports SENSITIVE_KEYS at
// templates/cli/lib/parser.ts:54 -- keep in the same order for reviewability.
var sensitiveKeys = []string{
	"secret",
	"apikey",
	"accesstoken",
	"refreshtoken",
	"password",
	"jwt",
	"clientsecret",
	"secretkey",
	"sessionsecret",
}

// normalizeKey lower-cases and strips everything that is not alphanumeric, so
// `API-Key`, `api_key` and `apiKey` all collapse to `apikey`.
func normalizeKey(key string) string {
	var builder strings.Builder
	builder.Grow(len(key))
	for _, r := range strings.ToLower(key) {
		if (r >= 'a' && r <= 'z') || (r >= '0' && r <= '9') {
			builder.WriteRune(r)
		}
	}

	return builder.String()
}

// IsSensitiveKey reports whether a field name names a credential.
//
// Suffix matching is deliberate: it catches `providerAccessToken` and
// `x-appwrite-key` style names without an exhaustive list.
func IsSensitiveKey(key string) bool {
	normalized := normalizeKey(key)
	for _, sensitive := range sensitiveKeys {
		if normalized == sensitive || strings.HasSuffix(normalized, sensitive) {
			return true
		}
	}

	return false
}

// IsSensitiveFlagName reports whether a command-line flag carries a credential
// as its value.
//
// Deliberately broader than IsSensitiveKey, and the difference matters twice:
//
//   - `--password-signer-key` normalises to `passwordsignerkey`, which ends in
//     `key` rather than in any listed term, so a suffix test misses it.
//   - `--key` and `-k` normalise to `key` and `k`, absent from the shared list
//     because `key` is an ordinary field name in API payloads and matching it
//     there would mask responses that are not credentials.
//
// A flag name carries neither ambiguity, so substring matching is safe here
// where it would not be on a response field.
func IsSensitiveFlagName(name string) bool {
	normalized := normalizeKey(name)
	if normalized == "key" || normalized == "k" {
		return true
	}
	for _, sensitive := range sensitiveKeys {
		if strings.Contains(normalized, sensitive) {
			return true
		}
	}

	return false
}

// MaskString redacts a credential, keeping just enough to identify which one it
// is.
//
// A key or token tail helps a user tell two credentials apart; a password tail
// is pure leakage, so passwords are masked whole regardless of length.
func MaskString(value, key string) string {
	if len(value) <= 16 || strings.Contains(strings.ToLower(key), "password") {
		return HiddenValue
	}

	// Prefixed credentials such as `standard_abc...` keep their prefix, which
	// identifies the credential type. The bound keeps at least the last four
	// characters out of the prefix.
	if index := strings.Index(value, "_"); index > 0 && index < len(value)-9 {
		return value[:index+1] + HiddenValue + value[len(value)-4:]
	}

	return HiddenValue + value[len(value)-4:]
}

// Redactor walks decoded JSON and masks credential-bearing fields.
//
// ShowSecrets mirrors the --show-secrets flag. Applied records whether anything
// was actually masked, which the renderer uses to decide whether to print the
// "values were hidden" footer.
type Redactor struct {
	ShowSecrets bool
	Applied     bool
}

// Mask returns a copy of value with sensitive fields replaced.
//
// key is the field name value was found under, empty at the root and for array
// elements -- an array of strings under a sensitive key is masked by the
// recursive call, not by the element's own (absent) name, which is why the TS
// passes undefined there and this passes "".
func (r *Redactor) Mask(value any, key string) any {
	if key != "" && IsSensitiveKey(key) && !r.ShowSecrets {
		r.Applied = true

		switch typed := value.(type) {
		case string:
			return MaskString(typed, key)
		case nil:
			return nil
		default:
			return HiddenValue
		}
	}

	switch typed := value.(type) {
	case []any:
		masked := make([]any, len(typed))
		for i, item := range typed {
			masked[i] = r.Mask(item, "")
		}

		return masked
	case *jsonx.Object:
		// Rebuilt in the same key order, so masking never reorders a response.
		masked := jsonx.NewObject()
		for _, name := range typed.Keys() {
			item, _ := typed.Get(name)
			masked.Set(name, r.Mask(item, name))
		}

		return masked
	case map[string]any:
		masked := make(map[string]any, len(typed))
		for name, item := range typed {
			masked[name] = r.Mask(item, name)
		}

		return masked
	case json.Number:
		return typed
	}

	return value
}
