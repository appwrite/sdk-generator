package sdk

// Environment variables that override the configured context.
//
// A CI job sets these expecting them to
// beat a checked-in appwrite.config.json, so they are consulted first.
const (
	EnvProjectID      = "APPWRITE_PROJECT_ID"
	EnvOrganizationID = "APPWRITE_ORGANIZATION_ID"
	EnvEndpoint       = "APPWRITE_ENDPOINT"
	EnvConfigFile     = "APPWRITE_CONFIG_FILE"
)
