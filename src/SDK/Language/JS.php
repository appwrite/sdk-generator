<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

abstract class JS extends Language
{
    /** Prettier's default print width, mirrored by the generated .prettierrc. */
    protected const int PRINT_WIDTH = 80;

    protected $params = [
        'npmPackage' => 'packageName',
        'bowerPackage' => 'packageName',
    ];

    /**
     * @return $this
     */
    public function setNPMPackage(string $name): self
    {
        $this->setParam('npmPackage', $name);

        return $this;
    }

    /**
     * @return $this
     */
    public function setBowerPackage(string $name): self
    {
        $this->setParam('bowerPackage', $name);

        return $this;
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            'abstract',
            'arguments',
            'await', // new in ECMAScript 5 and 6.
            'boolean',
            'break',
            'byte',
            'case',
            'catch',
            'char',
            'class', // new in ECMAScript 5 and 6.
            'console',
            'const',
            'continue',
            'debugger',
            'default',
            'delete',
            'do',
            'double',
            'else',
            'enum', // new in ECMAScript 5 and 6.
            'eval',
            'export', // new in ECMAScript 5 and 6.
            'extends', // new in ECMAScript 5 and 6.
            'false',
            'final',
            'finally',
            'float',
            'for',
            'function',
            'goto',
            'if',
            'implements',
            'import', // new in ECMAScript 5 and 6.
            'in',
            'instanceof',
            'int',
            'interface',
            'let', // new in ECMAScript 5 and 6.
            'long',
            'native',
            'new',
            'null',
            'package',
            'private',
            'protected',
            'public',
            'return',
            'short',
            'static',
            'super', // new in ECMAScript 5 and 6.
            'switch',
            'synchronized',
            'this',
            'throw',
            'throws',
            'transient',
            'true',
            'try',
            'typeof',
            'var',
            'void',
            'volatile',
            'while',
            'with',
            'yield',
            'path'
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? $type . '[]' : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER, self::TYPE_NUMBER => 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_ARRAY => $this->isUntypedNestedArray($parameter, $schema)
                ? 'any[][]'
                : $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . '[]',
            self::TYPE_FILE => 'File',
            self::TYPE_OBJECT => 'object',
            default => 'any',
        };
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type       = $this->getSchemaType($param);
        $default    = $this->getSchemaDefault($param);
        $required   = ($param instanceof Parameter && $param->required);

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$default}'";
                    break;
            }
        }

        return $output;
    }

    #[Override]
    public function keepsOpenEnumType(): bool
    {
        return true;
    }

    protected function getEnumTypeName(Schema|Parameter $parameter, ?Specification $spec = null): ?string
    {
        $schema = $this->getSchema($parameter);
        $enumSchema = $this->getEnumSchema($parameter);
        if ($enumSchema->enum === []) {
            return null;
        }

        $type = $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
        if ($this->isOpenStringEnum($parameter)) {
            $type = '(' . $type . ' | (string & {}))';
        }

        return $schema instanceof ArraySchema ? $type . '[]' : $type;
    }

    /**
     * Render an `if (...)` guard, breaking one operand per line when the
     * single-line form exceeds the print width.
     *
     * @param array<int, string> $operands
     */
    protected function formatGuard(array $operands, int $indent): string
    {
        $operands = array_values(array_filter(array_map(trim(...), $operands), fn(string $o): bool => $o !== ''));
        $pad = str_repeat(' ', $indent);
        $oneLine = $pad . 'if (' . implode(' && ', $operands) . ') {';

        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return 'if (' . implode(' && ', $operands) . ') {';
        }

        $inner = $pad . str_repeat(' ', 4);

        return "if (\n" . $inner . implode(" &&\n" . $inner, $operands) . "\n" . $pad . ') {';
    }

    /**
     * Render the `new Client()` setup chain for a documentation example.
     *
     * Prettier collapses a single-call chain onto one line (breaking the
     * argument out when it overflows) and keeps a multi-call chain broken.
     *
     * @param array<int, array{call: string, argument: string, comment: string}> $calls
     */
    protected function formatClientChain(array $calls): string
    {
        if ($calls === []) {
            return 'const client = new Client();';
        }

        if (count($calls) === 1) {
            $call = $calls[0];
            $comment = $call['comment'] === '' ? '' : ' // ' . $call['comment'];
            $oneLine = "const client = new Client()." . $call['call']
                . "('" . $call['argument'] . "');";

            if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
                return $oneLine . $comment;
            }

            return "const client = new Client()." . $call['call'] . "(\n    '"
                . $call['argument'] . "',\n);" . $comment;
        }

        $lines = 'const client = new Client()';
        $last = array_key_last($calls);
        foreach ($calls as $key => $call) {
            $comment = $call['comment'] === '' ? '' : ' // ' . $call['comment'];
            $lines .= "\n    ." . $call['call'] . "('" . $call['argument'] . "')"
                . ($key === $last ? ';' : '') . $comment;
        }

        return $lines;
    }

    /**
     * Render a simple assignment, moving the right-hand side onto its own
     * indented line when the single-line form exceeds the print width.
     */
    protected function formatAssignment(string $lhs, string $rhs, int $indent): string
    {
        $oneLine = str_repeat(' ', $indent) . $lhs . ' = ' . $rhs . ';';

        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return $lhs . ' = ' . $rhs . ';';
        }

        return $lhs . " =\n" . str_repeat(' ', $indent + 4) . $rhs . ';';
    }

    /**
     * Render one `key: value` entry of a documentation example, wrapping it the
     * way Prettier would when the single-line form overflows the print width.
     */
    protected function formatExampleEntry(string $key, string $value, string $comment): string
    {
        $oneLine = '    ' . $key . ': ' . $value . ',';
        if (mb_strlen($oneLine) <= self::PRINT_WIDTH || str_contains($value, "\n")) {
            return $key . ': ' . $value . ',' . $comment;
        }

        // An array literal breaks one element per line; anything else moves the
        // value onto its own indented line.
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $body = '';
            foreach ($this->splitTopLevel(mb_substr($value, 1, -1)) as $item) {
                $body .= '        ' . $item . ",\n";
            }

            return $key . ": [\n" . $body . '    ],' . $comment;
        }

        return $key . ":\n        " . $value . ',' . $comment;
    }

    /**
     * Split a list on a top-level delimiter, leaving quoted and bracketed
     * segments — and, when tracking generics, `<...>` segments — intact.
     *
     * @return array<int, string>
     */
    protected function splitTopLevel(string $body, string $delimiter = ',', bool $trackGenerics = false): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $quote = null;
        $escaped = false;

        $chars = str_split($body);
        foreach ($chars as $index => $char) {
            if ($quote !== null) {
                $current .= $char;
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"') {
                $quote = $char;
            } elseif (in_array($char, ['{', '[', '('], true)) {
                $depth++;
            } elseif (in_array($char, ['}', ']', ')'], true)) {
                $depth--;
            } elseif ($trackGenerics && $char === '<') {
                $depth++;
            } elseif ($trackGenerics && $char === '>' && ($chars[$index - 1] ?? '') !== '=') {
                // `=>` is an arrow, not a closing generic bracket.
                $depth--;
            }

            if ($char === $delimiter && $depth === 0) {
                if (trim($current) !== '') {
                    $parts[] = trim($current);
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    /**
     * Render a named import, expanding it across lines when the single-line
     * form would exceed Prettier's print width.
     *
     * @param array<int, string> $names
     */
    protected function formatNamedImport(array $names, string $module): string
    {
        $names = array_values(array_filter(array_map(trim(...), $names), fn(string $n): bool => $n !== ''));
        $oneLine = 'import { ' . implode(', ', $names) . " } from '" . $module . "';";

        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return $oneLine;
        }

        $body = '';
        foreach ($names as $name) {
            $body .= '    ' . $name . ",\n";
        }

        return "import {\n" . $body . "} from '" . $module . "';";
    }

    /**
     * Quote a string the way Prettier would: single quotes unless the value
     * itself contains one and no double quote.
     *
     * Control characters must be re-escaped. The example arrives as JSON, so
     * `json_decode` has already turned sequences like `\n` into real control
     * characters, and emitting one raw would produce an unterminated string
     * literal that Prettier cannot even parse.
     */
    protected function getStringLiteral(string $value): string
    {
        $quote = str_contains($value, "'") && !str_contains($value, '"') ? '"' : "'";

        // NUL deliberately has no `\0` shorthand here: `\0` followed by a digit
        // forms a legacy octal escape, which is a SyntaxError in strict mode and
        // silently decodes to the wrong character otherwise. `\x00` is always safe.
        $escaped = str_replace(
            ["\\", "\n", "\r", "\t", "\v", "\f", "\x08", "\u{2028}", "\u{2029}"],
            ['\\\\', '\\n', '\\r', '\\t', '\\v', '\\f', '\\b', '\\u2028', '\\u2029'],
            $value,
        );

        // Any remaining C0/C1 control character has no shorthand escape.
        $escaped = preg_replace_callback(
            '/[\x00-\x1F\x7F]/u',
            static fn(array $match): string => sprintf('\\x%02X', ord($match[0])),
            $escaped,
        ) ?? $escaped;

        if ($quote === "'") {
            $escaped = str_replace("'", "\\'", $escaped);
        }

        return $quote . $escaped . $quote;
    }

    /**
     * Render an object literal key, quoting it only when it is not a valid
     * ECMAScript identifier. Prettier removes redundant quotes, so emitting
     * them unconditionally would make generated output fail its own check.
     */
    protected function getObjectKeyLiteral(string $value): string
    {
        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $value)) {
            return $value;
        }

        return $this->getStringLiteral($value);
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toPascalCase($value)),
            new TwigFilter('jsGuard', fn(array $operands, int $indent = 8): string => $this->formatGuard($operands, $indent), ['is_safe' => ['html']]),
            new TwigFilter('jsClientChain', fn(array $calls): string => $this->formatClientChain($calls), ['is_safe' => ['html']]),
            new TwigFilter('jsAssign', fn(string $lhs, string $rhs, int $indent = 8): string => $this->formatAssignment($lhs, $rhs, $indent), ['is_safe' => ['html']]),
            new TwigFilter('jsExampleEntry', fn(string $key, string $value, string $comment = ''): string => $this->formatExampleEntry($key, $value, $comment), ['is_safe' => ['html']]),
            new TwigFilter('jsImport', fn(array $names, string $module): string => $this->formatNamedImport($names, $module), ['is_safe' => ['html']]),
            new TwigFilter('trimLines', fn(string $value): string => preg_replace('/[ \t]+$/m', '', $value) ?? $value),
            new TwigFilter('jsKey', fn(string $value): string => $this->getObjectKeyLiteral($value), ['is_safe' => ['html']]),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $this->getEnumSchema($param);
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $this->resolveEnumKeys($param);
                $enumName = $this->toPascalCase(($enumSchema instanceof StringSchema ? $enumSchema->enumName : null) ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;
                $prefix = $this->getPermissionPrefix();

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        return $this->toPascalCase($enumKeys[$index]);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        return $this->toPascalCase($enumValues[$index]);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    return $this->toPascalCase((string)$fallback);
                };

                if ($isArray) {
                    $values = [];
                    if (is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (is_array($example)) {
                        $values = $example;
                    }

                    if ($values === []) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(fn($value): string => $prefix . $enumName . '.' . $resolveKey($value), $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $prefix . $enumName . '.' . $resolveKey($value);
            }),
        ];
    }
}
