<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Rust extends Language
{
    protected $params = [
        "cratePackage" => "packageName",
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setCratePackage(string $name): self
    {
        $this->setParam("cratePackage", $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "Rust";
    }

    /**
     * Get Language Keywords List
     *
     * @return array
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
                "destination" => "src/models/{{ definition.name | caseSnake }}.rs",
                "template" => "rust/src/models/model.rs.twig",
            ],
            [
                "scope" => "requestModel",
                "destination" => "src/models/{{ requestModel.name | caseSnake }}.rs",
                "template" => "rust/src/models/request_model.rs.twig",
            ],
            [
                "scope" => "default",
                "destination" => "src/enums/mod.rs",
                "template" => "rust/src/enums/mod.rs.twig",
            ],
            [
                "scope" => "enum",
                "destination" => "src/enums/{{ enum.name | caseSnake }}.rs",
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

    /**
     * @param array $parameter
     * @param array $spec
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        // Handle enum types
        if (isset($parameter["enumName"])) {
            return "crate::enums::" . ucfirst($parameter["enumName"]);
        }
        if (!empty($parameter["enumValues"])) {
            return "crate::enums::" . ucfirst($parameter["name"]);
        }

        if (
            isset($parameter["type"]) && $parameter["type"] === "array" &&
            isset($parameter["items"]["type"]) && $parameter["items"]["type"] === "object" &&
            !isset($parameter["items"]["model"]) &&
            !isset($parameter["items"]['$ref'])
        ) {
            return "Vec<serde_json::Value>";
        }
        if (isset($parameter["items"])) {
            // Map definition nested type to parameter nested type
            $parameter["array"] = $parameter["items"];
        }

        return match ($parameter["type"]) {
            self::TYPE_INTEGER => "i64",
            self::TYPE_NUMBER => "f64",
            self::TYPE_FILE => "InputFile",
            self::TYPE_STRING => "String",
            self::TYPE_BOOLEAN => "bool",
            self::TYPE_OBJECT => "serde_json::Value",
            self::TYPE_ARRAY => isset($parameter["array"]["model"])
                ? "Vec<crate::models::" . ucfirst($parameter["array"]["model"]) . ">"
                : (!empty(($parameter["array"] ?? [])["type"]) && !\is_array($parameter["array"]["type"])
                    ? "Vec<" . $this->getTypeName($parameter["array"]) . ">"
                    : "Vec<String>"),
            default => isset($parameter["model"])
                ? "crate::models::" . ucfirst($parameter["model"])
                : $parameter["type"],
        };
    }

    /**
     * @return string
     */
    public function getStaticAccessOperator(): string
    {
        return '::';
    }

    /**
     * @return string
     */
    public function getStringQuote(): string
    {
        return '"';
    }

    /**
     * @param string $elements
     * @return string
     */
    public function getArrayOf(string $elements): string
    {
        return 'vec![' . $elements . ']';
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type = $param["type"] ?? "";
        $default = $param["default"] ?? "";
        $required = $param["required"] ?? "";

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
                    $output .= "String::from(\"" . addslashes($default) . "\")";
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

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
    {
        $type = $param["type"] ?? "";
        $example = $param["example"] ?? "";

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
                    $output .= "\"" . addslashes($example) . "\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= "serde_json::json!({})";
                    break;
                case self::TYPE_ARRAY:
                    if (preg_match('/^\[(.*)]$/s', $example, $match)) {
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

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                "rustdocComment",
                function ($value, $indent = 0) {
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
            new TwigFilter("propertyType", function (
                array $property,
                array $spec = [],
                string $generic = "serde_json::Value",
            ) {
                return $this->getPropertyType($property, $spec, $generic);
            }),
            new TwigFilter("returnType", function (
                array $method,
                array $spec,
                string $namespace,
                string $generic = "serde_json::Value",
            ) {
                return $this->getReturnType($method, $spec, $namespace, $generic);
            }),
            new TwigFilter("caseEnumKey", function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter("inputType", function (
                array $property,
                array $spec = [],
                string $generic = "serde_json::Value",
            ) {
                return $this->getInputType($property, $spec, $generic);
            }),
            new TwigFilter("paramValue", function (
                array $property,
                string $paramName,
                array $spec = [],
            ) {
                return $this->getParamValue($property, $paramName, $spec);
            }, ["is_safe" => ["html"]]),
            new TwigFilter("rustType", function ($value) {
                return str_replace(['&lt;', '&gt;'], ['<', '>'], $value);
            }, ["is_safe" => ["html"]]),
            new TwigFilter("rustCrateName", function ($value) {
                return str_replace('-', '_', $value);
            }),
            new TwigFilter("stripProtocol", function ($value) {
                return str_replace(['https://', 'http://'], '', $value);
            }),
        ];
    }

    /**
     * @param array $property
     * @param array $spec
     * @param string $generic
     * @return string
     */
    protected function getPropertyType(array $property, array $spec, string $generic = "serde_json::Value"): string
    {
        if (\array_key_exists("sub_schema", $property)) {
            $type = "crate::models::" . ucfirst($property["sub_schema"]);

            if ($property["type"] === "array") {
                $type = "Vec<" . $type . ">";
            }
        } else {
            $type = $this->getTypeName($property, $spec);
        }

        return $type;
    }

    /**
     * Get input type for method parameters (uses impl Into for better DX)
     *
     * @param array $property
     * @param array $spec
     * @param string $generic
     * @return string
     */
    protected function getInputType(array $property, array $spec, string $generic = "serde_json::Value"): string
    {
        $baseType = $this->getPropertyType($property, $spec, $generic);

        // For String types, accept impl Into<String> for better DX
        if ($baseType === "String") {
            return "impl Into<String>";
        }

        // For Vec<String>, accept impl IntoIterator for better DX (accepts slices, vecs, arrays)
        if ($baseType === "Vec<String>") {
            return "impl IntoIterator<Item = impl Into<String>>";
        }

        // For Vec<crate::enums::*>, keep as-is (enums don't benefit from Into)
        if (preg_match('/^Vec<crate::enums::/', $baseType)) {
            return $baseType;
        }

        // For Vec<crate::models::*>, keep as-is (models don't benefit from Into)
        if (preg_match('/^Vec<crate::models::/', $baseType)) {
            return $baseType;
        }

        // For primitive types (i64, f64, bool), keep as-is
        if (in_array($baseType, ['i64', 'f64', 'bool', 'InputFile', 'serde_json::Value'])) {
            return $baseType;
        }

        // For enum types, keep as-is
        if (str_starts_with($baseType, 'crate::enums::')) {
            return $baseType;
        }

        // For model types, keep as-is
        if (str_starts_with($baseType, 'crate::models::')) {
            return $baseType;
        }

        // For other Vec types with primitives
        if (preg_match('/^Vec<(i64|f64|bool|serde_json::Value)>$/', $baseType)) {
            return $baseType;
        }

        // Default: return base type as-is
        return $baseType;
    }

    /**
     * Get parameter value conversion expression for method body
     *
     * @param array $property
     * @param string $paramName
     * @param array $spec
     * @return string
     */
    protected function getParamValue(array $property, string $paramName, array $spec): string
    {
        $baseType = $this->getPropertyType($property, $spec);

        // For String types with impl Into<String>, call .into()
        if ($baseType === "String") {
            return $paramName . ".into()";
        }

        // For Vec<String> with impl IntoIterator, map and collect
        if ($baseType === "Vec<String>") {
            return $paramName . ".into_iter().map(|s| s.into()).collect::<Vec<String>>()";
        }

        // For other types, use as-is
        return $paramName;
    }

    /**
     * @param array $method
     * @param array $spec
     * @param string $namespace
     * @param string $generic
     * @return string
     */
    protected function getReturnType(
        array $method,
        array $spec,
        string $namespace,
        string $generic = "serde_json::Value",
    ): string {
        if ($method["type"] === "webAuth") {
            return "crate::error::Result<String>";
        }
        if ($method["type"] === "location") {
            return "crate::error::Result<Vec<u8>>";
        }

        if (
            \array_key_exists("responseModels", $method)
            && \count($method["responseModels"]) > 1
        ) {
            return "crate::error::Result<serde_json::Value>";
        }

        $isEmpty = empty($method["produces"]) || (isset($method["responses"]) && $this->isEmptyResponse($method["responses"]));

        if (
            !\array_key_exists("responseModel", $method) ||
            empty($method["responseModel"]) ||
            $method["responseModel"] === "any"
        ) {
            if ($isEmpty) {
                return "crate::error::Result<()>";
            }
            return "crate::error::Result<serde_json::Value>";
        }

        $ret = ucfirst($method["responseModel"]);

        return "crate::error::Result<crate::models::" . $ret . ">";
    }

    protected function isEmptyResponse(array $responses): bool
    {
        foreach ($responses as $code => $response) {
            if (!in_array((int)$code, [204, 205])) {
                return false;
            }
        }
        return !empty($responses);
    }

    /**
     * @param string|null $model
     * @param array $spec
     * @return bool
     */
    protected function hasGenericType(?string $model, array $spec): bool
    {
        if (empty($model) || $model === "any") {
            return false;
        }

        $model = $spec["definitions"][$model];

        if ($model["additionalProperties"] ?? false) {
            return true;
        }

        foreach ($model["properties"] as $property) {
            if (!\array_key_exists("sub_schema", $property) || !$property["sub_schema"]) {
                continue;
            }

            return $this->hasGenericType($property["sub_schema"], $spec);
        }

        return false;
    }
}
