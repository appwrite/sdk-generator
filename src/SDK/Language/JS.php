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

        if (mb_strlen($oneLine) <= 80) {
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

            if (mb_strlen($oneLine) <= 80) {
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

        if (mb_strlen($oneLine) <= 80) {
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
        if (mb_strlen($oneLine) <= 80 || str_contains($value, "\n")) {
            return $key . ': ' . $value . ',' . $comment;
        }

        // An array literal breaks one element per line; anything else moves the
        // value onto its own indented line.
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $items = $this->splitTopLevel(mb_substr($value, 1, -1));
            // A single element never earns its own line; the comment is what
            // pushed the entry over the width, and Prettier ignores it.
            if (count($items) > 1) {
                $body = '';
                foreach ($items as $item) {
                    $body .= '        ' . $item . ",\n";
                }

                return $key . ": [\n" . $body . '    ],' . $comment;
            }
        }

        return $key . ":\n        " . $value . ',' . $comment;
    }

    /**
     * Split a comma-separated list on top-level commas only.
     *
     * @return array<int, string>
     */
    protected function splitTopLevel(string $body): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $quote = null;

        foreach (str_split($body) as $char) {
            if ($quote !== null) {
                $current .= $char;
                if ($char === $quote) {
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
            }

            if ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
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

        if (mb_strlen($oneLine) <= 80) {
            return $oneLine;
        }

        $body = '';
        foreach ($names as $name) {
            $body .= '    ' . $name . ",\n";
        }

        return "import {\n" . $body . "} from '" . $module . "';";
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

        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
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
