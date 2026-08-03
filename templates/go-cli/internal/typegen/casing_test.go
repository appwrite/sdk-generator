package typegen

import "testing"

// Baselines captured by running the TypeScript regexes through node, not read
// off the source. Every generated type name, field name and enum key goes
// through these, so a difference here renames things in users' generated code.
func TestCasingMatchesTypeScript(t *testing.T) {
	cases := []struct {
		input, kebab, snake, upper, camel, pascal, enumKey string
	}{
		{"userName", "user-name", "user_name", "USER_NAME", "userName", "UserName", "USER_NAME"},
		{"UserID", "user-id", "user_id", "USER_ID", "userId", "UserId", "USER_ID"},
		{"HTTPServer", "http-server", "http_server", "HTTP_SERVER", "httpServer", "HttpServer", "HTTP_SERVER"},
		{"my_field name", "my-field-name", "my_field_name", "MY_FIELD_NAME", "myFieldName", "MyFieldName", "MY_FIELD_NAME"},
		{"  spaced  out ", "spaced-out", "spaced_out", "SPACED_OUT", "spacedOut", "SpacedOut", "SPACED_OUT"},
		{"123abc", "123abc", "123abc", "123ABC", "123abc", "123abc", "_123ABC"},
		{"", "", "", "", "", "", "_"},
		// Invalid characters are dropped, not replaced: `$id` becomes `id`.
		{"$id", "id", "id", "ID", "id", "Id", "ID"},
		// camelCase eats the doubled hyphen, so this is aBC and not aBC-style.
		{"a-b--c", "a-b-c", "a_b_c", "A_B_C", "aBC", "ABC", "A_B_C"},
		{"XMLHttpRequest", "xml-http-request", "xml_http_request", "XML_HTTP_REQUEST", "xmlHttpRequest", "XmlHttpRequest", "XML_HTTP_REQUEST"},
		{"already-kebab", "already-kebab", "already_kebab", "ALREADY_KEBAB", "alreadyKebab", "AlreadyKebab", "ALREADY_KEBAB"},
		// Not mixed-case-99: the acronym rule splits MiXeD unexpectedly.
		{"MiXeD_Case-99", "mi-xe-d-case-99", "mi_xe_d_case_99", "MI_XE_D_CASE_99", "miXeDCase99", "MiXeDCase99", "MI_XE_D_CASE_99"},
		{"!!!", "", "", "", "", "", "_"},
		{"9lives", "9lives", "9lives", "9LIVES", "9lives", "9lives", "_9LIVES"},
	}

	for _, tc := range cases {
		t.Run(tc.input, func(t *testing.T) {
			check := func(name, got, want string) {
				if got != want {
					t.Errorf("%s(%q) = %q, want %q", name, tc.input, got, want)
				}
			}
			check("ToKebabCase", ToKebabCase(tc.input), tc.kebab)
			check("ToSnakeCase", ToSnakeCase(tc.input), tc.snake)
			check("ToUpperSnakeCase", ToUpperSnakeCase(tc.input), tc.upper)
			check("ToCamelCase", ToCamelCase(tc.input), tc.camel)
			check("ToPascalCase", ToPascalCase(tc.input), tc.pascal)
			check("SanitizeEnumKey", SanitizeEnumKey(tc.input), tc.enumKey)
		})
	}
}
