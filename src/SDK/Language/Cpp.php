<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

/**
 * Cpp Class
 *
 * SDK generator for C++ using cpr for HTTP requests and nlohmann/json for JSON parsing.
 */
class Cpp extends Language
{
    private ?array $identifierOverridesCache = null;

    private function buildPathLine(array $method): string
    {
        $path = $method['path'];
        $pathFormat = preg_replace('/\{([^}]+)\}/', '{}', $path);
        $pathParams = [];

        foreach ($method['parameters']['path'] ?? [] as $parameter) {
            $name = $this->escapeKeyword($this->toCamelCase($parameter['name']));
            if (!empty($parameter['enumValues']) || !empty($parameter['enumName'])) {
                $pathParams[] = 'appwrite::enums::toString(' . $name . ')';
            } else {
                $pathParams[] = $name;
            }
        }

        return 'std::string path_ = std::format("' . addslashes($pathFormat) . '"' . (empty($pathParams) ? '' : ', ' . implode(', ', $pathParams)) . ");";
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'C++';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'alignas',
            'alignof',
            'and',
            'and_eq',
            'asm',
            'atomic_cancel',
            'atomic_commit',
            'atomic_noexcept',
            'auto',
            'bitand',
            'bitor',
            'bool',
            'break',
            'case',
            'catch',
            'char',
            'char8_t',
            'char16_t',
            'char32_t',
            'class',
            'compl',
            'concept',
            'const',
            'consteval',
            'constexpr',
            'constinit',
            'const_cast',
            'continue',
            'co_await',
            'co_return',
            'co_yield',
            'decltype',
            'default',
            'delete',
            'do',
            'double',
            'dynamic_cast',
            'else',
            'enum',
            'explicit',
            'export',
            'extern',
            'false',
            'float',
            'for',
            'friend',
            'goto',
            'if',
            'inline',
            'int',
            'long',
            'mutable',
            'namespace',
            'new',
            'noexcept',
            'not',
            'not_eq',
            'nullptr',
            'operator',
            'or',
            'or_eq',
            'private',
            'protected',
            'public',
            'reflexpr',
            'register',
            'reinterpret_cast',
            'requires',
            'return',
            'short',
            'signed',
            'sizeof',
            'static',
            'static_assert',
            'static_cast',
            'struct',
            'switch',
            'template',
            'this',
            'thread_local',
            'throw',
            'try',
            'typedef',
            'typeid',
            'typename',
            'union',
            'unsigned',
            'using',
            'virtual',
            'void',
            'volatile',
            'wchar_t',
            'while',
            'xor',
            'xor_eq',

            // Context-sensitive - safe to add defensively
            'final',
            'override',
            'import',
            'module'
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        if ($this->identifierOverridesCache !== null) {
            return $this->identifierOverridesCache;
        }

        // Only truly special cases that need a specific name
        $special = [
            'default' => 'default_',
        ];

        // Auto-generate the rest from keywords list
        $auto = [];
        foreach ($this->getKeywords() as $keyword) {
            if (!isset($special[$keyword])) {
                $auto[$keyword] = $keyword . '_';
            }
        }

        return $this->identifierOverridesCache = array_merge($auto, $special);
    }

    public function escapeKeyword(string $value): string
    {
        $overrides = $this->getIdentifierOverrides();

        if (isset($overrides[$value])) {
            return $overrides[$value];
        }

        // Safety net: name wasn't in overrides but IS a keyword
        if (in_array($value, $this->getKeywords(), true)) {
            return $value . '_';
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope' => 'default',
                'destination' => 'CMakeLists.txt',
                'template' => 'cpp/CMakeLists.txt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'README.md',
                'template' => 'cpp/README.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/client.hpp',
                'template' => 'cpp/include/client.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/base.hpp',
                'template' => 'cpp/include/base.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/core.hpp',
                'template' => 'cpp/include/core.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/models.hpp',
                'template' => 'cpp/include/models.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/services.hpp',
                'template' => 'cpp/include/services.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/tests.cpp',
                'template' => 'cpp/tests/tests_main.cpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/model_tests.cpp',
                'template' => 'cpp/tests/model_test.cpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/integration.cpp',
                'template' => 'cpp/tests/integration.cpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/integration_logic.cpp',
                'template' => 'cpp/tests/integration_logic.cpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'include/appwrite/enums/enums.hpp',
                'template' => 'cpp/include/enums/enums.hpp.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'CHANGELOG.md',
                'template' => 'cpp/CHANGELOG.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'LICENSE',
                'template' => 'cpp/LICENSE.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => '.github/workflows/stale.yml',
                'template' => 'cpp/.github/workflows/stale.yml',
            ],
            [
                'scope' => 'copy',
                'destination' => '.github/workflows/autoclose.yml',
                'template' => 'cpp/.github/workflows/autoclose.yml',
            ],
            [
                'scope' => 'default',
                'destination' => '.github/workflows/publish.yml',
                'template' => 'cpp/.github/workflows/publish.yml.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'examples/basic_usage.cpp',
                'template' => 'cpp/examples/basic_usage.cpp.twig',
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
        return $this->getTypeNameInternal($parameter);
    }

    /**
     * @param array $parameter
     * @return string
     */
    private function getTypeNameInternal(array $parameter): string
    {
        $isArray = (($parameter['type'] ?? null) === self::TYPE_ARRAY);
        $baseType = '';

        if (!$isArray && isset($parameter['enumName'])) {
            $baseType = 'appwrite::enums::' . $this->toPascalCase($parameter['enumName']);
        } elseif ($isArray && isset($parameter['enumName'])) {
            $baseType = 'std::vector<appwrite::enums::' . $this->toPascalCase($parameter['enumName']) . '>';
        } else {
            if (isset($parameter['items'])) {
                $parameter['array'] = $parameter['items'];
            }

            $baseType = match ($parameter['type']) {
                self::TYPE_INTEGER => 'int64_t',
                self::TYPE_NUMBER => 'double',
                self::TYPE_FILE => 'appwrite::InputFile',
                self::TYPE_STRING => 'std::string',
                self::TYPE_BOOLEAN => 'bool',
                self::TYPE_OBJECT => 'nlohmann::json',
                self::TYPE_ARRAY => isset($parameter['array']['model'])
                ? 'std::vector<appwrite::models::' . $this->toPascalCase($parameter['array']['model']) . '>'
                : (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])
                    ? 'std::vector<' . $this->getTypeNameInternal($parameter['array']) . '>'
                    : 'std::vector<std::string>'),
                default => isset($parameter['model'])
                ? 'appwrite::models::' . $this->toPascalCase($parameter['model'])
                : $parameter['type'],
            };
        }

        if (!($parameter['required'] ?? true) && $baseType !== 'appwrite::InputFile') {
            return 'std::optional<' . $baseType . '>';
        }

        return $baseType;
    }

    /**
     * @param array $parameter
     * @return string
     */
    private function getInputTypeInternal(array $parameter): string
    {
        return $this->toInputType($this->getTypeNameInternal($parameter));
    }

    /**
     * @param string $type
     * @return string
     */
    protected function toInputType(string $type): string
    {
        // 1. Base case: std::string -> std::string_view
        if ($type === 'std::string') {
            return 'std::string_view';
        }

        if ($type === 'nlohmann::json') {
            return 'const nlohmann::json&';
        }

        // 2. Recursive Case: std::optional<T> -> std::optional<toInputType(T)>
        if (str_starts_with($type, 'std::optional<') && str_ends_with($type, '>')) {
            $inner = substr($type, 14, -1);
            $innerMapped = ($inner === 'nlohmann::json') ? 'nlohmann::json' : $this->toInputType($inner);
            return 'std::optional<' . $innerMapped . '>';
        }

        // 3. Recursive Case: std::vector<T> -> std::vector<T> (keep std::string, don't recurse to string_view)
        if (str_starts_with($type, 'std::vector<') && str_ends_with($type, '>')) {
            $inner = substr($type, 12, -1);
            // Keep std::string (not std::string_view) inside vectors: brace-init from literals must work
            $innerMapped = ($inner === 'nlohmann::json') ? 'nlohmann::json' : $inner;
            return 'std::vector<' . $innerMapped . '>';
        }

        return $type;
    }

    private function getReturnTypeInternal(array $method, array $spec, string $namespace = 'appwrite', bool $async = false): string
    {
        $raw = $this->getRawReturnTypeInternal($method, $spec, $namespace);
        $result = 'Result<' . $raw . '>';

        return $async ? 'Task<' . $result . '>' : $result;
    }

    private function getRawReturnTypeInternal(array $method, array $spec, string $namespace = 'appwrite'): string
    {
        if ($method['type'] === 'webAuth') {
            return 'std::string';
        }
        if ($method['type'] === 'location') {
            return 'BinaryResponse';
        }

        if (isset($method['responseModels']) && count($method['responseModels']) > 1) {
            return 'nlohmann::json';
        }

        $isEmpty = empty($method['produces']) ||
            (isset($method['responses']) && $this->isEmptyResponse($method['responses']));

        if (empty($method['responseModel']) || $method['responseModel'] === 'any') {
            return $isEmpty ? 'void' : 'nlohmann::json';
        }

        return $namespace . '::models::' . $this->toPascalCase($method['responseModel']);
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
     * @param string $elements Comma-separated elements
     * @return string
     */
    public function getArrayOf(string $elements): string
    {
        return 'std::vector<std::string>{' . $elements . '}';
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getParamDefault(array $parameter): string
    {
        return $this->getParamDefaultInternal($parameter);
    }

    /**
     * @param array $parameter
     * @return string
     */
    private function getParamDefaultInternal(array $parameter): string
    {
        $type = $parameter['type'] ?? '';
        $default = $parameter['default'] ?? null;
        $required = $parameter['required'] ?? true;

        if (!$required && $default === null) {
            return ' = std::nullopt';
        }

        if ($default === null) {
            return '';
        }

        $output = ' = ';

        if (isset($parameter['enumValues']) && !empty($parameter['enumValues'])) {
            $enumValues = $parameter['enumValues'];
            $enumKeys = $parameter['enumKeys'] ?? [];
            $enumName = $this->toPascalCase($parameter['enumName'] ?? $parameter['name'] ?? '');

            $resolveKey = function ($value) use ($enumValues, $enumKeys) {
                $index = array_search($value, $enumValues, true);
                if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                    return $this->toUpperSnakeCase($enumKeys[$index]);
                }
                if ($index !== false && isset($enumValues[$index])) {
                    return $this->toUpperSnakeCase($enumValues[$index]);
                }
                $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                return $this->toUpperSnakeCase((string) $fallback);
            };

            if ($type === self::TYPE_ARRAY) {
                $values = [];
                if (\is_string($default) && $default !== '') {
                    $decoded = json_decode($default, true);
                    if (\is_array($decoded)) {
                        $values = $decoded;
                    }
                } elseif (\is_array($default)) {
                    $values = $default;
                }

                if (empty($values)) {
                    // return empty vector for array array initialization without default
                    return $output . '{}';
                }

                $items = array_map(function ($value) use ($enumName, $resolveKey) {
                    return 'appwrite::enums::' . $enumName . '::' . $resolveKey($value);
                }, $values);

                return $output . '{ ' . implode(', ', $items) . ' }';
            }

            $value = ($default !== null && $default !== '') ? $default : $enumValues[0];
            return $output . 'appwrite::enums::' . $enumName . '::' . $resolveKey($value);
        }

        switch ($type) {
            case self::TYPE_NUMBER:
            case self::TYPE_INTEGER:
                $output .= (is_numeric($default)) ? $default : '0';
                break;
            case self::TYPE_BOOLEAN:
                if (is_bool($default)) {
                    $output .= $default ? 'true' : 'false';
                } else {
                    $output .= ($default === 'true' || $default === '1') ? 'true' : 'false';
                }
                break;
            case self::TYPE_STRING:
                $output .= '"' . addslashes((string) $default) . '"';
                break;
            case self::TYPE_OBJECT:
                if ($default === '' || $default === '[]' || $default === '{}') {
                    $output .= 'nlohmann::json::object()';
                } else {
                    $output .= 'nlohmann::json::parse("' . addslashes(is_string($default) ? $default : json_encode($default)) . '")';
                }
                break;
            case self::TYPE_ARRAY:
                // Determine correct vector type to avoid brace-init mismatch
                $innerItemType = $parameter['array']['type'] ?? self::TYPE_STRING;
                $isModel = isset($parameter['array']['model']);
                $vecType = 'std::vector<std::string>';
                if ($innerItemType === self::TYPE_OBJECT || $isModel) {
                    $vecType = 'std::vector<nlohmann::json>';
                } elseif ($innerItemType === self::TYPE_INTEGER) {
                    $vecType = 'std::vector<int64_t>';
                }

                if ($default === '' || $default === '[]') {
                    $output .= $vecType . '{}';
                } else {
                    $decoded = is_string($default) ? json_decode($default, true) : $default;
                    if (is_array($decoded)) {
                        $items = array_map(function ($v) {
                            if (is_bool($v)) {
                                return $v ? 'true' : 'false';
                            }
                            if (is_numeric($v)) {
                                return (string)$v;
                            }
                            return '"' . addslashes((string) $v) . '"';
                        }, $decoded);
                        $output .= $vecType . '{' . implode(', ', $items) . '}';
                    } else {
                        $output .= $vecType . '{}';
                    }
                }
                break;
            case self::TYPE_FILE:
                $output .= 'appwrite::InputFile()';
                break;
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
        $type = $param['type'] ?? '';
        $example = $param['example'] ?? '';

        $output = '';

        switch ($type) {
            case self::TYPE_NUMBER:
            case self::TYPE_INTEGER:
                $output .= ($example === '') ? '0' : $example;
                break;
            case self::TYPE_BOOLEAN:
                if ($example === '') {
                    $output .= 'false';
                } elseif (is_bool($example)) {
                    $output .= $example ? 'true' : 'false';
                } else {
                    $output .= ($example === 'true' || $example === '1') ? 'true' : 'false';
                }
                break;
            case self::TYPE_STRING:
                $output .= ($example === '') ? '""' : '"' . addslashes((string) $example) . '"';
                break;
            case self::TYPE_OBJECT:
                if ($example === '') {
                    $output .= '{}';
                } else {
                    $parsed = is_string($example) ? json_decode($example) : $example;
                    if ($parsed !== null || json_last_error() === JSON_ERROR_NONE) {
                        $output .= 'nlohmann::json::parse("' . addslashes(is_string($example) ? $example : json_encode($example)) . '")';
                    } else {
                        $output .= 'nlohmann::json::object()';
                    }
                }
                break;
            case self::TYPE_ARRAY:
                if ($example === '' || $example === '[]') {
                    $output .= '{}';
                } else {
                    $decoded = is_string($example) ? json_decode($example, true) : $example;
                    if (is_array($decoded)) {
                        $items = array_map(function ($v) {
                            if (is_bool($v)) {
                                return $v ? 'true' : 'false';
                            }
                            if (is_numeric($v)) {
                                return $v;
                            }
                            return '"' . addslashes((string) $v) . '"';
                        }, $decoded);
                        $output .= '{' . implode(', ', $items) . '}';
                    } else {
                        $output .= '{}';
                    }
                }
                break;
            case self::TYPE_FILE:
                $output .= 'appwrite::InputFile::fromPath("file.png")';
                break;
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cppdocComment', function (array $method, array $spec, int $indent = 0) {
                $indentStr = str_repeat(' ', $indent);
                $result = $indentStr . "/**\n";

                // Description
                $description = wordwrap(trim($method['description'] ?? $method['title'] ?? ''), 75, "\n" . $indentStr . " * ");
                $result .= $indentStr . " * " . $description . "\n";
                $result .= $indentStr . " *\n";

                // Parameters
                foreach ($method['parameters']['all'] ?? [] as $parameter) {
                    $name = $this->escapeKeyword($this->toCamelCase($parameter['name']));
                    $desc = trim($parameter['description'] ?? '');
                    $result .= $indentStr . " * @param " . $name . " " . $desc . "\n";
                }

                // Return type
                $returnType = $this->getReturnTypeInternal($method, $spec);
                $result .= $indentStr . " * @return " . $returnType . "\n";

                $result .= $indentStr . " */";
                return $result;
            }, ['is_safe' => ['html']]),
            new TwigFilter('casePascal', function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter('propertyType', function (array $property, array $spec = []) {
                return $this->getPropertyType($property, $spec);
            }),
            new TwigFilter('caseUpperSnakeCase', function (string $value) {
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('inputType', function (array $property, array $spec = []) {
                return $this->getInputTypeInternal($property);
            }),

            new TwigFilter('nodiscardDoc', function ($value) {
                return '[[nodiscard]] ' . $value;
            }),
            new TwigFilter('returnType', function (array $method, array $spec, string $namespace = 'appwrite', bool $async = false) {
                return $this->getReturnTypeInternal($method, $spec, $namespace, $async);
            }),
            new TwigFilter('rawReturnType', function (array $method, array $spec, string $namespace = 'appwrite') {
                return $this->getRawReturnTypeInternal($method, $spec, $namespace);
            }),
            new TwigFilter('pathFormattingBlock', function (array $method) {
                return $this->buildPathLine($method);
            }),
            new TwigFilter('parameterBuildingBlock', function (array $method, array $spec) {
                $output = $this->buildPathLine($method) . "\n\n";

                $output .= "nlohmann::json params = nlohmann::json::object();\n";
                foreach ($method['parameters']['query'] ?? [] as $parameter) {
                    $name = $this->escapeKeyword($this->toCamelCase($parameter['name']));
                    // Only plain strings need the std::string() cast; enums are already typed
                    $isPlainStr = ($parameter['type'] ?? '') === self::TYPE_STRING
                        && empty($parameter['enumValues']);
                    if (!($parameter['required'] ?? true)) {
                        $output .= 'if (' . $name . '.has_value()) {' . "\n";
                        if ($isPlainStr) {
                            $val = 'std::string(' . $name . '.value())';
                        } else {
                            $val = '*' . $name;
                        }
                        $output .= '    params["' . $parameter['name'] . '"] = ' . $val . ";\n";
                        $output .= "}\n";
                    } else {
                        $val = $isPlainStr ? 'std::string(' . $name . ')' : $name;
                        $output .= 'params["' . $parameter['name'] . '"] = ' . $val . ";\n";
                    }
                }

                foreach ($method['parameters']['body'] ?? [] as $parameter) {
                    $name = $this->escapeKeyword($this->toCamelCase($parameter['name']));
                    $isOptional = !($parameter['required'] ?? true);
                    $indent = $isOptional ? '    ' : '';
                    if ($isOptional) {
                        $output .= 'if (' . $name . '.has_value()) {' . "\n";
                    }

                    if ($parameter['type'] === 'file') {
                        if ($method['type'] !== 'upload') {
                            $output .= $indent . 'params["' . $parameter['name'] . '"] = ' . ($isOptional ? '*' . $name : $name) . ".toJson();\n";
                        }
                    } else {
                        // Only plain strings need std::string() cast; enums are C++ enum class types
                        $isPlainStr = ($parameter['type'] ?? '') === self::TYPE_STRING
                            && empty($parameter['enumValues']);
                        if ($isOptional) {
                            $val = $isPlainStr ? 'std::string(' . $name . '.value())' : '*' . $name;
                        } else {
                            $val = $isPlainStr ? 'std::string(' . $name . ')' : $name;
                        }
                        $output .= $indent . 'params["' . $parameter['name'] . '"] = ' . $val . ";\n";
                    }

                    if ($isOptional) {
                        $output .= "}\n";
                    }
                }

                $output .= "\nstd::unordered_map<std::string, std::string> local_headers_;\n";
                foreach ($method['parameters']['header'] ?? [] as $parameter) {
                    $name = $this->escapeKeyword($this->toCamelCase($parameter['name']));
                    if (!($parameter['required'] ?? true)) {
                        $output .= 'if (' . $name . '.has_value()) {' . "\n";
                        $output .= '    local_headers_["' . $parameter['name'] . '"] = *' . $name . ";\n";
                        $output .= "}\n";
                    } else {
                        $output .= 'local_headers_["' . $parameter['name'] . '"] = ' . $name . ";\n";
                    }
                }

                return $output;
            }),
            new TwigFilter('parameterDispatchBlock', function (array $method, array $spec) {
                return $this->getDispatchBlock($method, $spec, false);
            }),
            new TwigFilter('parameterDispatchAsyncBlock', function (array $method, array $spec) {
                return $this->getDispatchBlock($method, $spec, true);
            }),
            new TwigFilter('exampleJson', function (array $property) {
                $type = $property['type'] ?? '';
                $example = $property['example'] ?? null;
                $subSchema = $property['sub_schema'] ?? null;

                if ($example !== null) {
                    if ($type === self::TYPE_ARRAY) {
                        // Already a JSON array string
                        if (is_string($example) && str_starts_with(ltrim($example), '[')) {
                            return $example;
                        }
                        // If the array element type is a sub_schema (object), emit [{}] instead
                        if ($subSchema !== null) {
                            return '[{}]';
                        }
                        // Wrap scalar / non-array example into a JSON array
                        if (is_array($example)) {
                            return json_encode($example);
                        }
                        return '[' . json_encode($example) . ']';
                    }
                    if ($type === self::TYPE_OBJECT) {
                        if (is_string($example) && str_starts_with(ltrim($example), '{')) {
                            return $example;
                        }
                        if (is_array($example)) {
                            return json_encode($example);
                        }
                        return '{}';
                    }
                    return json_encode($example);
                }

                switch ($type) {
                    case self::TYPE_INTEGER:
                    case self::TYPE_NUMBER:
                        return "0";
                    case self::TYPE_BOOLEAN:
                        return "false";
                    case self::TYPE_STRING:
                        return '""';
                    case self::TYPE_ARRAY:
                        // For arrays of sub_schema objects, emit minimal object array
                        return $subSchema !== null ? '[{}]' : '[]';
                    case self::TYPE_OBJECT:
                        return "{}";
                    default:
                        return "null";
                }
            }, ['is_safe' => ['html']]),


            new TwigFilter('indent', function ($value, $count = 4) {
                return str_repeat(' ', $count) . str_replace("\n", "\n" . str_repeat(' ', $count), $value);
            }),
            new TwigFilter('enumName', function (array $property) {
                if (isset($property['enumName']) && $property['enumName'] !== '') {
                    return $this->toPascalCase($property['enumName']);
                }
                return null;
            }),

            new TwigFilter('topoSort', function (array $definitions) {
                // Build a map from definition name -> definition
                $byName = [];
                foreach ($definitions as $def) {
                    $name = $def['name'] ?? '';
                    if ($name !== '') {
                        $byName[$name] = $def;
                    }
                }

                // Kahn's algorithm: compute dependencies
                $deps = []; // name -> set of names this depends on
                foreach ($byName as $name => $def) {
                    $deps[$name] = [];
                    foreach ($def['properties'] ?? [] as $prop) {
                        $sub = $prop['sub_schema'] ?? null;
                        if ($sub !== null && isset($byName[$sub]) && $sub !== $name) {
                            $deps[$name][$sub] = true;
                        }
                    }
                }

                // Topological sort (DFS post-order)
                $visited = [];
                $sorted  = [];

                $visit = null;
                $visit = function (string $name) use (&$visit, &$visited, &$sorted, &$deps, &$byName) {
                    if (isset($visited[$name])) {
                        return;
                    }
                    $visited[$name] = true;
                    foreach (array_keys($deps[$name] ?? []) as $dep) {
                        $visit($dep);
                    }
                    if (isset($byName[$name])) {
                        $sorted[] = $byName[$name];
                    }
                };

                foreach (array_keys($byName) as $name) {
                    $visit($name);
                }

                return $sorted;
            }),
        ];
    }

    /**
     * @param array $method
     * @param array $spec
     * @param bool $async
     * @return string
     */
    protected function getDispatchBlock(array $method, array $spec, bool $async): string
    {
        $verb = strtoupper($method['method']);
        $suffix = $async ? 'Async' : '';
        $inner = $this->getRawReturnTypeInternal($method, $spec);

        if ($method['type'] === 'upload') {
            $fileKey = '';
            $fileParam = '';
            foreach ($method['parameters']['all'] ?? [] as $p) {
                if (($p['type'] ?? '') === 'file') {
                    $fileKey = $p['name'];
                    $fileParam = $this->escapeKeyword($this->toCamelCase($p['name']));
                    break;
                }
            }
            $progress = $fileParam !== '' ? ', std::move(onProgress)' : '';
            return "return client_.fileUpload{$suffix}<{$inner}>(\"{$verb}\", path_, \"{$fileKey}\", std::move({$fileParam}), std::move(local_headers_), std::move(params){$progress});";
        }

        if ($method['type'] === 'webAuth') {
            return "return client_.callLocation{$suffix}(\"{$verb}\", path_, std::move(local_headers_), std::move(params));";
        }
        if ($method['type'] === 'location') {
            return "return client_.callBytes{$suffix}(\"{$verb}\", path_, std::move(local_headers_), std::move(params));";
        }
        if ($inner === 'void') {
            return "return client_.callVoid{$suffix}(\"{$verb}\", path_, std::move(local_headers_), std::move(params));";
        }

        return "return client_.call{$suffix}<{$inner}>(\"{$verb}\", path_, std::move(local_headers_), std::move(params));";
    }

    /**
     * @param array $property
     * @param array $spec
     * @return string
     */
    protected function getPropertyType(array $property, array $spec): string
    {
        if (\array_key_exists('sub_schema', $property)) {
            $type = 'appwrite::models::' . $this->toPascalCase($property['sub_schema']);

            if ($property['type'] === 'array') {
                $type = 'std::vector<' . $type . '>';
            }

            if (!($property['required'] ?? true)) {
                $type = 'std::optional<' . $type . '>';
            }
        } else {
            $type = $this->getTypeName($property, $spec);
        }

        return $type;
    }

    /**
     * @param array $responses
     * @return bool
     */
    private function isEmptyResponse(array $responses): bool
    {
        foreach ($responses as $code => $response) {
            if (!in_array((int) $code, [204, 205])) {
                return false;
            }
        }
        return !empty($responses);
    }
}
