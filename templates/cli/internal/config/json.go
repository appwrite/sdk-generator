package config

import "github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"

// Object and Marshal live in internal/jsonx because internal/output needs them
// too, and an output package importing config for a JSON primitive would be
// the wrong dependency. Aliased here so config's own callers keep one import.
type Object = jsonx.Object

// NewObject returns an empty ordered object.
func NewObject() *Object { return jsonx.NewObject() }

// Marshal renders a value the way the established CLI writes config files.
func Marshal(value any) ([]byte, error) { return jsonx.Marshal(value) }
