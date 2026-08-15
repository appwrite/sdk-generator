package sdk

// Environment variables that override the configured context.
//
// A CI job sets these expecting them to
// beat a checked-in appwrite.config.json, so they are consulted first.
//
// These live outside sdk.go because that file is emitted as a stub in the
// conformance build -- generated commands call mock services there, so nothing
// constructs a client -- and the CLI still needs these names in both builds.
// `client --debug` reads them to report the effective project, and referencing
// them from the conditional half broke the conformance build with "undefined:
// sdk.EnvProjectID" while every ordinary build kept compiling.
const (
	EnvProjectID      = "APPWRITE_PROJECT_ID"
	EnvOrganizationID = "APPWRITE_ORGANIZATION_ID"
	EnvEndpoint       = "APPWRITE_ENDPOINT"
)
