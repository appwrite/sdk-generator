<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Rust extends Language
{
    #[Override]
    protected $params = [
        "cratePackage" => "packageName",
    ];

    /**
     * @return $this
     */
    public function setCratePackage(string $name): self
    {
        $this->setParam("cratePackage", $name);

        return $this;
    }

    public function getName(): string
    {
        return "Rust";
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            "abstract",
            "as",
            "become",
            "box",
            "break",
            "const",
            "continue",
            "crate",
            "do",
            "else",
            "enum",
            "extern",
            "false",
            "final",
            "fn",
            "for",
            "if",
            "impl",
            "in",
            "let",
            "loop",
            "macro",
            "match",
            "mod",
            "move",
            "mut",
            "override",
            "priv",
            "pub",
            "ref",
            "return",
            "self",
            "static",
            "struct",
            "super",
            "trait",
            "true",
            "type",
            "typeof",
            "unsafe",
            "unsized",
            "use",
            "virtual",
            "where",
            "while",
            "yield",
            "async",
            "await",
            "dyn",
            "union",
            "gen",
            "try",
            "Self",
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [
            "type" => "r#type",
            "ref" => "r#ref",
            "move" => "r#move",
            "static" => "r#static",
            "const" => "r#const",
            "struct" => "r#struct",
            "enum" => "r#enum",
            "trait" => "r#trait",
            "impl" => "r#impl",
            "fn" => "r#fn",
            "let" => "r#let",
            "mut" => "r#mut",
            "use" => "r#use",
            "pub" => "r#pub",
            "crate" => "r#crate",
            "mod" => "r#mod",
            "super" => "r#super",
            "self" => "r#self",
            "where" => "r#where",
            "async" => "r#async",
            "gen" => "r#gen",
            "try" => "r#try",
            "Self" => "r#Self",
            "await" => "r#await",
            "loop" => "r#loop",
            "while" => "r#while",
            "for" => "r#for",
            "if" => "r#if",
            "else" => "r#else",
            "match" => "r#match",
            "return" => "r#return",
            "break" => "r#break",
            "continue" => "r#continue",
        ];
    }

    public function getFiles(): array
    {
        return [
            [
                "scope" => "default",
                "destination" => "Cargo.toml",
                "template" => "rust/Cargo.toml.twig",
            ],
            [
                "scope" => "default",
                "destination" => "README.md",
                "template" => "rust/README.md.twig",
            ],
            [
                "scope" => "method",
                "destination" => "docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md",
                "template" => "rust/docs/example.md.twig",
            ],
            [
                "scope" => "default",
                "destination" => "CHANGELOG.md",
                "template" => "rust/CHANGELOG.md.twig",
            ],
            [
                "scope" => "default",
                "destination" => "LICENSE",
                "template" => "rust/LICENSE.twig",
            ],
            [
                "scope" => "copy",
                "destination" => ".gitignore",
                "template" => "rust/.gitignore",
            ],
            [
                "scope" => "default",
                "destination" => "src/lib.rs",
                "template" => "rust/src/lib.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/client.rs",
                "template" => "rust/src/client.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/error.rs",
                "template" => "rust/src/error.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/input_file.rs",
                "template" => "rust/src/input_file.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/query.rs",
                "template" => "rust/src/query.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/permission.rs",
                "template" => "rust/src/permission.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/role.rs",
                "template" => "rust/src/role.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/id.rs",
                "template" => "rust/src/id.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/operator.rs",
                "template" => "rust/src/operator.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/models/mod.rs",
                "template" => "rust/src/models/mod.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/services/mod.rs",
                "template" => "rust/src/services/mod.rs.twig",
            ],
            [
                "scope" => "service",
                "destination" => "src/services/{{ service.name | caseSnake }}.rs",
                "template" => "rust/src/services/service.rs.twig",
            ],

            [
                "scope" => "definition",
                "destination" => "src/models/{{ definitionName | caseSnake }}.rs",
                "template" => "rust/src/models/model.rs.twig",
            ],
            [
                "scope" => "requestModel",
                "destination" => "src/models/{{ requestModelName | caseSnake }}.rs",
                "template" => "rust/src/models/request_model.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/enums/mod.rs",
                "template" => "rust/src/enums/mod.rs.twig",
            ],
            [
                "scope" => "enum",
                "destination" => "src/enums/{{ enum.title | caseSnake }}.rs",
                "template" => "rust/src/enums/enum.rs.twig",
            ],
            [
                "scope" => "copy",
                "destination" => "tests/tests.rs",
                "template" => "rust/tests/tests.rs",
            ],
            [
                "scope" => "default",
                "destination" => ".github/workflows/publish.yml",
                "template" => "rust/.github/workflows/publish.yml.twig",
            ],
            [
                "scope" => "copy",
                "destination" => ".github/workflows/stale.yml",
                "template" => "rust/.github/workflows/stale.yml",
            ],
            [
                "scope" => "copy",
                "destination" => ".github/workflows/autoclose.yml",
                "template" => "rust/.github/workflows/autoclose.yml",
            ],
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum !== []) {
            $type = 'crate::enums::' . $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            return $schema instanceof ArraySchema ? 'Vec<' . $type . '>' : $type;
        }

        $model = $schema instanceof ArraySchema
            ? $this->getArraySchemaModel($schema)
            : $this->getSchemaModel($schema);
        if ($model !== null) {
            $type = $model === 'any' ? 'serde_json::Value' : 'crate::models::' . $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? 'Vec<' . $type . '>' : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'i64',
            self::TYPE_NUMBER => 'f64',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_STRING => 'String',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_OBJECT => 'serde_json::Value',
            self::TYPE_ARRAY => 'Vec<' . $this->getTypeName($schema->items) . '>',
            default => 'serde_json::Value',
        };
    }

    public function getStaticAccessOperator(): string
    {
        return '::';
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return 'vec![' . $elements . ']';
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type = $this->getSchemaType($param);
        $default = $this->getSchemaDefault($param);
        $required = $param instanceof Parameter && $param->required;

        if ($required) {
            return "";
        }

        $output = " = ";

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= "0";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= "false";
                    break;
                case self::TYPE_STRING:
                    $output .= "String::new()";
                    break;
                case self::TYPE_OBJECT:
                    $output .= "serde_json::Value::Null";
                    break;
                case self::TYPE_ARRAY:
                    $output .= "Vec::new()";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::default()";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= $default ? "true" : "false";
                    break;
                case self::TYPE_STRING:
                    $output .= "String::from(\"" . addslashes((string) $default) . "\")";
                    break;
                case self::TYPE_OBJECT:
                    $output .= "serde_json::Value::Null";
                    break;
                case self::TYPE_ARRAY:
                    $output .= "Vec::new()";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::default()";
                    break;
            }
        }

        return $output;
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type = $this->getSchemaType($param);
        $example = $this->getSchemaExample($param);

        $output = "";

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= "0";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= "false";
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= "serde_json::json!({})";
                    break;
                case self::TYPE_ARRAY:
                    $output .= "vec![]";
                    break;
                case self::TYPE_FILE:
                    $output .= 'InputFile::from_path("file.png")';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= $example ? "true" : "false";
                    break;
                case self::TYPE_STRING:
                    $output .= "\"" . addslashes((string) $example) . "\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= "serde_json::json!({})";
                    break;
                case self::TYPE_ARRAY:
                    if (\is_string($example) && $this->isPermissionString($example)) {
                        return $this->getPermissionExample($example);
                    }

                    $items = $this->getArraySchema($param) ?? $this->getSchema($param);

                    if (\is_string($example) && $example !== "") {
                        $decoded = json_decode($example, true);

                        if (\is_array($decoded)) {
                            $formatted = array_map(
                                fn ($value): string => $this->formatArrayItemExample($value, $items),
                                $decoded,
                            );

                            return "vec![" . implode(", ", $formatted) . "]";
                        }
                    } elseif (\is_array($example)) {
                        $formatted = array_map(
                            fn ($value): string => $this->formatArrayItemExample($value, $items),
                            $example,
                        );

                        return "vec![" . implode(", ", $formatted) . "]";
                    }

                    if (preg_match('/^\[(.*)]$/s', (string) $example, $match)) {
                        $example = $match[1];
                    }
                    $output .= "vec![" . $example . "]";
                    break;
                case self::TYPE_FILE:
                    $output .= 'InputFile::from_path("file.png")';
                    break;
            }
        }

        return $output;
    }

    #[Override]
    public function getPermissionExample(string $example): string
    {
        $permissions = [];

        foreach ($this->extractPermissionParts($example) as $permission) {
            $action = $this->transformPermissionAction($permission["action"]);
            $roleName = $this->transformPermissionRole($permission["role"]);
            $roleId = $permission["id"] ?? null;
            $innerRole = $permission["innerRole"] ?? null;

            if ($roleId === null && str_contains($roleName, "/")) {
                [$roleName, $innerRole] = explode("/", $roleName, 2);
            }

            $roleExpr = match ($roleName) {
                "any" => "Role::any()",
                "guests" => "Role::guests()",
                "users" => "Role::users(" . ($innerRole !== null ? 'Some("' . addslashes($innerRole) . '")' : "None") . ")",
                "user" => 'Role::user("' . addslashes((string)$roleId) . '", ' . ($innerRole !== null ? 'Some("' . addslashes($innerRole) . '")' : "None") . ")",
                "team" => 'Role::team("' . addslashes((string)$roleId) . '", ' . ($innerRole !== null ? 'Some("' . addslashes($innerRole) . '")' : "None") . ")",
                "member" => 'Role::member("' . addslashes((string)$roleId) . '")',
                "label" => 'Role::label("' . addslashes((string)$roleId) . '")',
                default => 'Role::from("' . addslashes($roleName) . '")',
            };

            $permissions[] = "Permission::{$action}({$roleExpr}).to_string()";
        }

        return $this->getArrayOf(implode(", ", $permissions));
    }

    protected function getInputType(Schema|Parameter $property, ?Specification $spec = null): string
    {
        return match ($type = $this->getTypeName($property, $spec)) {
            'String' => 'impl Into<String>',
            'Vec<String>' => 'impl IntoIterator<Item = impl Into<String>>',
            default => $type,
        };
    }

    protected function getParamValue(Schema|Parameter $property, string $paramName, ?Specification $spec = null): string
    {
        return match ($this->getTypeName($property, $spec)) {
            'String' => $paramName . '.into()',
            'Vec<String>' => $paramName . '.into_iter().map(|s| s.into()).collect::<Vec<String>>()',
            default => $paramName,
        };
    }

    protected function getReturnType(Operation $method): string
    {
        return match ($method->extensions['x-appwrite']['type'] ?? '') {
            'webAuth' => 'crate::error::Result<String>',
            'location' => 'crate::error::Result<Vec<u8>>',
            default => $this->getResponseReturnType($method),
        };
    }

    protected function getResponseReturnType(Operation $method): string
    {
        $models = \array_values(\array_filter(
            $this->getOperationResponseModels($method),
            static fn(string $model): bool => $model !== 'any',
        ));
        if (\count($models) > 1) {
            return 'crate::error::Result<serde_json::Value>';
        }
        if ($models !== []) {
            return 'crate::error::Result<crate::models::' . $this->toPascalCase($models[0]) . '>';
        }

        // Emptiness follows the produced content types, not the response
        // codes: a 204 whose produced type is recorded in x-appwrite still
        // returns a body to deserialize, and narrowing it to `()` would be a
        // breaking change for every caller binding the result.
        return $this->getProducedTypes($method) === []
            ? 'crate::error::Result<()>'
            : 'crate::error::Result<serde_json::Value>';
    }

    /** @return list<string> */
    protected function getProducedTypes(Operation $method): array
    {
        $produces = [];
        foreach ($method->responses as $response) {
            foreach (\array_keys($response->content) as $contentType) {
                if ($contentType !== '' && !\in_array($contentType, $produces, true)) {
                    $produces[] = $contentType;
                }
            }
        }
        if ($produces === []) {
            $produces = $method->extensions['x-appwrite']['produces'] ?? [];
        }
        return $produces;
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                "rustdocComment",
                function ($value, $indent = 0): string {
                    $value = trim($value);
                    $value = explode("\n", $value);
                    $indent = \str_repeat(" ", $indent);
                    foreach ($value as $key => $line) {
                        $value[$key] = "/// " . wordwrap(trim($line), 75, "\n" . $indent . "/// ");
                    }
                    return implode("\n" . $indent, $value);
                },
                ["is_safe" => ["html"]],
            ),
            new TwigFilter("propertyType", fn(Schema $property, ?Specification $spec = null, string $generic = "serde_json::Value"): string => $this->getTypeName($property, $spec)),
            new TwigFilter("returnType", fn(Operation $method, Specification $spec, string $namespace, string $generic = "serde_json::Value"): string => $this->getReturnType($method)),
            new TwigFilter("caseEnumKey", fn(string $value): string => $this->toPascalCase($value)),
            new TwigFilter("docsArgumentExample", function (Schema|Parameter $param, string $crateName): string {
                if ($this->getSchemaType($param) === self::TYPE_FILE) {
                    $value = $this->toCaseSnake($param instanceof Parameter ? $param->name : 'file');
                    if (isset($this->getIdentifierOverrides()[$value])) {
                        $value = $this->getIdentifierOverrides()[$value];
                    }
                } elseif ($this->getSchema($param)->enum !== [] || ($this->getSchema($param) instanceof ArraySchema && $this->getSchema($param)->items->enum !== [])) {
                    $schema = $this->getSchema($param);
                    $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                    $enumName = $this->toPascalCase($this->getSchemaEnumName($param));
                    $example = $this->getSchemaExample($param) ?? $enumSchema->enum[0];

                    // An array parameter carries its example as a JSON string,
                    // so every member has to be resolved separately — searching
                    // for the whole string finds nothing and silently renders
                    // enum[0] instead.
                    $members = [$example];
                    if ($schema instanceof ArraySchema) {
                        $decoded = \is_string($example) ? \json_decode($example, true) : $example;
                        $members = \is_array($decoded) && $decoded !== [] ? $decoded : [$enumSchema->enum[0] ?? $example];
                    }

                    $variants = [];
                    foreach ($members as $member) {
                        $index = \array_search($member, $enumSchema->enum, true);
                        $key = $index === false
                            ? $member
                            : ($enumSchema->extensions['x-enum-keys'][$index] ?? $enumSchema->enum[$index] ?? $member);
                        if ((string) $key === '') {
                            continue;
                        }
                        $variants[] = $crateName . '::enums::' . $enumName . '::' . $this->toPascalCase((string) $key);
                    }

                    $value = $schema instanceof ArraySchema
                        ? 'vec![' . \implode(', ', $variants) . ']'
                        : ($variants[0] ?? '');
                } else {
                    $value = $this->getParamExample($param);
                }
                $required = $param instanceof Parameter && $param->required;
                return $required && !$this->getSchema($param)->nullable ? $value : "Some({$value})";
            }, ["is_safe" => ["html"]]),
            new TwigFilter("inputType", fn(Schema|Parameter $property, ?Specification $spec = null, string $generic = "serde_json::Value"): string => $this->getInputType($property, $spec)),
            new TwigFilter("paramValue", fn(Schema|Parameter $property, string $paramName, ?Specification $spec = null): string => $this->getParamValue($property, $paramName, $spec), ["is_safe" => ["html"]]),
            new TwigFilter("rustType", fn($value): string|array => str_replace(['&lt;', '&gt;'], ['<', '>'], $value), ["is_safe" => ["html"]]),
            new TwigFilter("rustCrateName", fn($value): string|array => str_replace('-', '_', $value)),
            new TwigFilter("stripProtocol", fn($value): string|array => str_replace(['https://', 'http://'], '', $value)),
        ];
    }
    /**
     * Snake-case using the same algorithm as the caseSnake Twig filter (SDK.php),
     * so PHP-generated variable references match template-generated declarations.
     */
    protected function toCaseSnake(string $value): string
    {
        preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $value, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match === strtoupper($match)
                ? strtolower($match)
                : lcfirst($match);
        }

        return implode('_', $ret);
    }

    protected function formatArrayItemExample(mixed $value, Schema $items): string
    {
        $itemType = $this->getSchemaType($items);

        return match ($itemType) {
            self::TYPE_INTEGER, self::TYPE_NUMBER => (string)$value,
            self::TYPE_BOOLEAN => $value ? "true" : "false",
            self::TYPE_OBJECT => "serde_json::json!(" . json_encode($value, JSON_UNESCAPED_SLASHES) . ")",
            self::TYPE_STRING => '"' . addslashes((string)$value) . '"' . ".into()",
            default => match (true) {
                \is_string($value) => '"' . addslashes($value) . '"' . ".into()",
                \is_bool($value) => $value ? "true" : "false",
                \is_int($value), \is_float($value) => (string)$value,
                \is_array($value) => "serde_json::json!(" . json_encode($value, JSON_UNESCAPED_SLASHES) . ")",
                default => "serde_json::Value::Null",
            },
        };
    }
}
