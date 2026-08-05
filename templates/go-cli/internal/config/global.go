package config

import (
	"net/url"
	"os"
	"path/filepath"
	"strconv"
	"strings"
)

// Preference keys stored in prefs.json. Names match the TypeScript CLI exactly:
// the same file is read and written by both binaries during the migration.
const (
	PreferenceCurrent      = "current"
	PreferenceEndpoint     = "endpoint"
	PreferenceEmail        = "email"
	PreferenceSelfSigned   = "selfSigned"
	PreferenceCookie       = "cookie"
	PreferenceProject      = "project"
	PreferenceKey          = "key"
	PreferenceLocale       = "locale"
	PreferenceMode         = "mode"
	PreferenceAccessToken  = "accessToken"
	PreferenceRefreshToken = "refreshToken"
	PreferenceTokenExpiry  = "tokenExpiry"
	PreferenceClientID     = "clientId"
)

// DefaultEndpoint is used when a session records none.
const DefaultEndpoint = "https://cloud.appwrite.io/v1"

const (
	ModeAdmin      = "admin"
	ModeDefault    = "default"
	ProjectConsole = "console"
)

// ignoredAttributes are top-level keys in prefs.json that are settings rather
// than sessions. Everything else at the top level is a session ID.
var ignoredAttributes = map[string]bool{
	PreferenceCurrent:      true,
	PreferenceSelfSigned:   true,
	PreferenceEndpoint:     true,
	PreferenceCookie:       true,
	PreferenceProject:      true,
	PreferenceKey:          true,
	PreferenceLocale:       true,
	PreferenceMode:         true,
	PreferenceAccessToken:  true,
	PreferenceRefreshToken: true,
	PreferenceTokenExpiry:  true,
	PreferenceClientID:     true,
}

// Global is the user-level preferences file, `~/.appwrite/prefs.json`.
type Global struct {
	path string
	data *Object
}

// Session is one stored login.
type Session struct {
	ID       string
	Endpoint string
	Email    string
}

// GlobalPath returns the default preferences path for the current user.
func GlobalPath(executableName string) (string, error) {
	home, err := os.UserHomeDir()
	if err != nil {
		return "", err
	}

	return filepath.Join(home, "."+executableName, "prefs.json"), nil
}

// LoadGlobal reads the preferences file. A missing or unparseable file yields
// empty preferences rather than an error, matching the TypeScript `read()`,
// which swallows failures so a corrupt file never blocks `login`.
func LoadGlobal(path string) *Global {
	global := &Global{path: path, data: NewObject()}

	contents, err := os.ReadFile(path)
	if err != nil {
		return global
	}

	parsed := NewObject()
	if err := parsed.UnmarshalJSON(contents); err != nil {
		return global
	}
	global.data = parsed

	return global
}

// Path returns the file backing these preferences.
func (g *Global) Path() string {
	return g.path
}

// Write persists preferences, creating the directory if needed.
//
// 0600 on the file and 0700 on the directory: this holds access and refresh
// tokens. The directory has to be tightened explicitly, because MkdirAll leaves
// an existing one alone and this file is shared with the TypeScript CLI, so on
// most machines it is already there -- a TypeScript user upgrading has it at
// 0755 and prefs.json at 0644. The file needs no such fix-up: the atomic write
// renames a fresh 0600 file into place, which discards the old mode along with
// the old contents.
func (g *Global) Write() error {
	directory := filepath.Dir(g.path)
	if err := os.MkdirAll(directory, 0o700); err != nil {
		return err
	}

	// Best-effort: the directory may predate this CLI and belong to someone else,
	// and failing to tighten it is not a reason to refuse to save a session --
	// which would leave the user unable to log in at all.
	_ = os.Chmod(directory, 0o700)

	encoded, err := Marshal(g.data)
	if err != nil {
		return err
	}

	return writeFileAtomically(g.path, encoded, 0o600)
}

// CurrentSessionID returns the active session ID, or "" if none is set.
func (g *Global) CurrentSessionID() string {
	return g.data.GetString(PreferenceCurrent)
}

// SetCurrentSessionID marks a session active.
func (g *Global) SetCurrentSessionID(id string) {
	g.data.Set(PreferenceCurrent, id)
}

// SessionIDs lists stored session IDs in file order.
func (g *Global) SessionIDs() []string {
	ids := []string{}
	for _, key := range g.data.Keys() {
		if !ignoredAttributes[key] {
			ids = append(ids, key)
		}
	}

	return ids
}

// Session returns one stored session.
func (g *Global) Session(id string) (Session, bool) {
	entry := g.data.GetObject(id)
	if entry == nil {
		return Session{}, false
	}

	return Session{
		ID:       id,
		Endpoint: entry.GetString(PreferenceEndpoint),
		Email:    entry.GetString(PreferenceEmail),
	}, true
}

// SessionData returns the raw stored values for one session, or nil when the
// session does not exist.
func (g *Global) SessionData(id string) *Object {
	if id == "" {
		return nil
	}

	return g.data.GetObject(id)
}

// Current returns the active session's stored values.
func (g *Global) Current() *Object {
	id := g.CurrentSessionID()
	if id == "" {
		return nil
	}

	return g.data.GetObject(id)
}

// CurrentValue reads one field from the active session.
func (g *Global) CurrentValue(key string) string {
	current := g.Current()
	if current == nil {
		return ""
	}

	return current.GetString(key)
}

// CurrentBool reads a boolean field off the active session.
//
// The value is written by `client --self-signed` as a real JSON boolean, but a
// prefs.json edited by hand -- or written by the TypeScript CLI, which stores
// whatever the user typed -- can hold the string "true" instead. Both are
// accepted, because rejecting the string form would silently disable a setting
// the user believes is on.
func (g *Global) CurrentBool(key string) bool {
	current := g.Current()
	if current == nil {
		return false
	}

	value, ok := current.Get(key)
	if !ok {
		return false
	}

	switch typed := value.(type) {
	case bool:
		return typed
	case string:
		parsed, err := strconv.ParseBool(typed)

		return err == nil && parsed
	default:
		return false
	}
}

// SetCurrentValue writes one field on the active session, creating the session
// entry if it does not exist yet.
func (g *Global) SetCurrentValue(key string, value any) {
	id := g.CurrentSessionID()
	if id == "" {
		return
	}
	current := g.data.GetObject(id)
	if current == nil {
		current = NewObject()
		g.data.Set(id, current)
	}
	current.Set(key, value)
}

// AddSession stores a session and makes it current.
func (g *Global) AddSession(id string, values *Object) {
	g.data.Set(id, values)
	g.SetCurrentSessionID(id)
}

// DeleteSession removes a session. When it was the active one, the `current`
// pointer is moved to another session if any remain, so the file is never left
// pointing at a session that no longer exists.
func (g *Global) DeleteSession(id string) {
	g.data.Delete(id)

	if g.CurrentSessionID() != id {
		return
	}

	remaining := g.SessionIDs()
	if len(remaining) == 0 {
		g.data.Delete(PreferenceCurrent)

		return
	}
	g.SetCurrentSessionID(remaining[0])
}

// Ports CLOUD_REGION_CODES and CLOUD_BASE_HOSTNAMES from
// templates/cli/lib/utils.ts:449.
//
// Hardcoded rather than derived from the spec because the TypeScript hardcodes
// it too, and this list decides which endpoints count as Cloud. Deriving it
// would let a spec change silently reclassify a user's endpoint.
var (
	cloudRegionCodes = map[string]bool{
		"fra": true, "nyc": true, "syd": true, "sfo": true, "sgp": true, "tor": true,
	}
	cloudBaseHostnames = map[string]bool{
		"cloud.appwrite.io":         true,
		"cloud.staging.appwrite.io": true,
	}
)

// baseCloudHostname strips a regional prefix such as `fra.` from a Cloud
// hostname, returning "" when the host is not Appwrite Cloud.
//
// Only a single label is stripped, and only when it is a known region code.
// Matching on suffix alone would treat any `*.cloud.appwrite.io` host as Cloud,
// including one an attacker controls, and normalising it would let a session
// stored for real Cloud be selected for that endpoint.
//
// JavaScript's URL parser lower-cases hostnames
// and Go's does not, hence the explicit fold.
func baseCloudHostname(hostname string) string {
	hostname = strings.ToLower(hostname)
	if cloudBaseHostnames[hostname] {
		return hostname
	}

	region, base, found := strings.Cut(hostname, ".")
	if !found {
		return ""
	}
	if cloudBaseHostnames[base] && cloudRegionCodes[region] {
		return base
	}

	return ""
}

// CloudBaseHost returns the Appwrite Cloud base hostname behind an endpoint,
// and false when the endpoint is self-hosted.
//
// Exported so callers that need to build a regional URL do not re-derive the
// region rules and drift from baseCloudHostname().
func CloudBaseHost(endpoint string) (string, bool) {
	parsed, err := url.Parse(endpoint)
	if err != nil || parsed.Hostname() == "" {
		return "", false
	}
	base := baseCloudHostname(parsed.Hostname())

	return base, base != ""
}

// NormalizeCloudConsoleEndpoint collapses a regional Cloud endpoint onto its
// base host, leaving self-hosted endpoints untouched.
func NormalizeCloudConsoleEndpoint(endpoint string) string {
	parsed, err := url.Parse(endpoint)
	if err != nil || parsed.Hostname() == "" {
		return endpoint
	}

	base := baseCloudHostname(parsed.Hostname())
	if base == "" {
		return endpoint
	}

	return "https://" + base + "/v1"
}

// IsCloudLoginEndpoint reports whether an endpoint signs in through the
// browser rather than with an email and a password.
//
// The TypeScript also accepts
// localhost behind the `devCloudLogin` feature flag; there is no flag registry
// in this port, so localhost is treated as self-hosted -- which is what it is
// for everyone outside Appwrite.
func IsCloudLoginEndpoint(endpoint string) bool {
	parsed, err := url.Parse(endpoint)
	if err != nil {
		return false
	}

	return strings.HasSuffix(parsed.Hostname(), ".appwrite.io")
}

// IsRegionalCloudEndpoint reports whether an endpoint names a Cloud REGION,
// like fra.cloud.appwrite.io, rather than the base host.
func IsRegionalCloudEndpoint(endpoint string) bool {
	parsed, err := url.Parse(endpoint)
	if err != nil || parsed.Hostname() == "" {
		return false
	}

	base := baseCloudHostname(parsed.Hostname())

	return base != "" && base != parsed.Hostname()
}

// EndpointsMatch compares two endpoints for session matching, normalising
// regional Cloud hosts and ignoring trailing slashes.
func EndpointsMatch(a, b string) bool {
	trim := func(value string) string {
		return strings.TrimRight(NormalizeCloudConsoleEndpoint(value), "/")
	}

	return trim(a) == trim(b)
}

// LegacyEmail marks a session recovered from the pre-sessions prefs format,
// where there was no email to record. Ports the literal in generic.ts:migrate.
const LegacyEmail = "legacy"

// MigrateLegacySession lifts a pre-sessions prefs.json into the sessions array,
// reporting whether it changed anything.
//
// Ports migrate() (generic.ts:392), which the TypeScript CLI runs on every
// invocation. prefs.json used to hold a single endpoint and cookie at the top
// level; sessions replaced that with a keyed array plus `current`. Without this,
// a user upgrading from an older CLI keeps a perfectly good cookie in a shape
// nothing reads, so the first command they run tells them they are logged out.
//
// Both keys have to be present. Either one alone is not a legacy session: a
// bare endpoint is what `client --endpoint` writes before anyone signs in.
func (g *Global) MigrateLegacySession(id string) bool {
	if !g.data.Has(PreferenceEndpoint) || !g.data.Has(PreferenceCookie) {
		return false
	}

	session := NewObject()
	session.Set(PreferenceEndpoint, g.data.GetString(PreferenceEndpoint))
	session.Set(PreferenceCookie, g.data.GetString(PreferenceCookie))
	session.Set(PreferenceEmail, LegacyEmail)

	// AddSession makes it current, which is what migrate() does by calling
	// setCurrentSession -- a legacy prefs.json has exactly one login, so there is
	// nothing else it could switch to.
	g.AddSession(id, session)
	g.data.Delete(PreferenceEndpoint)
	g.data.Delete(PreferenceCookie)

	return true
}
