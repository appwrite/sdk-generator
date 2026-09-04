<?php

namespace Appwrite\SDK\Language;

use InvalidArgumentException;
use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use stdClass;
use Twig\TwigFilter;

class Web extends JS
{
    public function getName(): string
    {
        return 'Web';
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return "'";
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/index.ts',
                'template'      => 'web/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'web/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.ts',
                'template'      => 'web/src/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'web/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/realtime.ts',
                'template'      => 'web/src/services/realtime.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'web/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'web/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'web/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'web/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/channel.ts',
                'template'      => 'web/src/channel.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'web/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'web/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'web/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'web/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'web/LICENSE.twig',
            ],
            [
            'scope'         => 'default',
            'destination'   => 'package.json',
            'template'      => 'web/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'web/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/web/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.mjs',
                'template'      => '/web/rollup.config.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/cjs/package.json',
                'template'      => '/web/dist/cjs/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/esm/package.json',
                'template'      => '/web/dist/esm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'web/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.title | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => 'web/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.npmrc',
                'template'      => 'web/.npmrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierrc',
                'template'      => 'web/.prettierrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierignore',
                'template'      => 'web/.prettierignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'eslint.config.mjs',
                'template'      => 'web/eslint.config.mjs',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'web/package-lock.json.twig',
            ],
        ];
    }

    /**
     * Render an object example as a JavaScript object literal rather than JSON.
     *
     * The spec supplies these as JSON, but the generated snippet is JavaScript,
     * and Prettier rewrites bare-identifier keys and single-quoted strings. The
     * example has to be emitted in that shape or the docs fail their own check.
     */
    protected function getObjectExample(string $example): string
    {
        if ($example === '{}' || $example === '') {
            return '{}';
        }

        if ($example === '[]') {
            return '[]';
        }

        // Decoding to stdClass rather than associative arrays keeps the JSON
        // object-versus-array distinction: with assoc arrays a nested `[]`
        // would render as `{}`, and `{"0": "a"}` as `['a']`.
        $decoded = json_decode($example);
        if (!is_array($decoded) && !$decoded instanceof stdClass) {
            return $example;
        }

        return preg_replace('/\n/', "\n    ", $this->renderJsValue($decoded, 1)) ?? $example;
    }

    /**
     * Recursively render a decoded JSON value as JavaScript source.
     *
     * `$prefix` is the width of whatever precedes the value on its first
     * line beyond the structural indent — the `key: ` of an enclosing
     * object entry — so the inline-fit test measures the real line.
     */
    protected function renderJsValue(mixed $value, int $depth, int $prefix = 0): string
    {
        $pad = str_repeat('    ', $depth);
        $closePad = str_repeat('    ', $depth - 1);

        if (is_array($value) && $value !== []) {
            $rendered = array_map(fn(mixed $item): string => $this->renderJsValue($item, $depth + 1), $value);

            // Prettier always breaks an array whose elements are themselves all
            // arrays or objects, unless there is only one of them. Otherwise it
            // keeps the array on one line while it fits.
            $allComposite = count($value) > 1
                && array_all($value, fn(mixed $item): bool => is_array($item) || $item instanceof stdClass);

            $inline = '[' . implode(', ', $rendered) . ']';
            if (
                !$allComposite
                && !str_contains($inline, "\n")
                && $prefix + mb_strlen($pad . $inline) <= self::PRINT_WIDTH
            ) {
                return $inline;
            }

            $items = '';
            foreach ($rendered as $item) {
                $items .= $pad . $item . ",\n";
            }

            return "[\n" . $items . $closePad . ']';
        }

        if (is_array($value)) {
            return '[]';
        }

        if ($value instanceof stdClass) {
            $value = get_object_vars($value);
            if ($value === []) {
                return '{}';
            }

            $entries = '';
            foreach ($value as $key => $item) {
                $keyLiteral = $this->getObjectKeyLiteral((string) $key);
                $entries .= $pad . $keyLiteral . ': '
                    . $this->renderJsValue($item, $depth + 1, mb_strlen($keyLiteral) + 2) . ",\n";
            }

            return "{\n" . $entries . $closePad . '}';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $this->getStringLiteral((string) $value);
    }

    /**
     * The example value used for a file parameter.
     *
     * Overridden by targets that upload from a path rather than a browser
     * file input. It is the only part of the example rendering that differs
     * between the JavaScript targets, so everything else stays shared.
     */
    protected function getFileExample(): string
    {
        return 'document.getElementById(\'uploader\').files[0]';
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => $this->getFileExample(),
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example)
                ? $this->getPermissionExample($example)
                : $this->getObjectExample((string) $example),
            self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_FILE => $this->getFileExample(),
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => $this->getObjectExample((string) $example),
            self::TYPE_STRING => $this->getStringLiteral((string) $example),
        };
    }

    #[Override]
    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        if (($type = $this->getEnumTypeName($parameter, $spec)) !== null) {
            return $type;
        }

        $models = $this->getSchemaModels($parameter);
        if ($models !== []) {
            $type = \implode(' | ', \array_map(fn(string $model): string => 'Models.' . $this->toPascalCase($model), $models));
            return $schema instanceof ArraySchema ? '(' . $type . ')[]' : $type;
        }

        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER, self::TYPE_NUMBER => $schema->format === 'int64' ? 'number | bigint' : 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_FILE => 'File',
            self::TYPE_ARRAY => $this->isUntypedNestedArray($parameter, $schema)
                ? 'any[][]'
                : $this->getTypeName($this->getArraySchema($parameter) ?? $schema, $spec) . '[]',
            self::TYPE_OBJECT => 'Record<string, any>',
            default => 'any',
        };
    }

    /** @return list<string> */
    protected function getGenericTypes(string $modelName, Specification $spec, bool $skipFirst = false, array $visited = []): array
    {
        if (isset($visited[$modelName])) {
            return [];
        }

        // `any` carries additionalProperties but is deliberately not emitted as
        // a model, so a generic parameterised on it would reference a type that
        // does not exist. Keep this in step with the exclusion in SDK::getDefinitions().
        if ($modelName === 'any') {
            return [];
        }

        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return [];
        }

        $visited[$modelName] = true;
        $generics = [];
        if (!$skipFirst && $model->additionalProperties) {
            $generics[] = $this->toPascalCase($modelName);
        }
        foreach ($model->properties as $property) {
            foreach ($this->getSchemaModels($property) as $dependency) {
                \array_push($generics, ...$this->getGenericTypes($dependency, $spec, false, $visited));
            }
        }
        return \array_values(\array_unique($generics));
    }

    public function getGenerics(string $modelName, Specification $spec, bool $skipFirst = false): string
    {
        $generics = \array_map(
            static fn(string $type): string => "{$type} extends Models.{$type} = Models.Default{$type}",
            $this->getGenericTypes($modelName, $spec, $skipFirst),
        );
        return $generics === [] ? '' : '<' . \implode(', ', $generics) . '>';
    }

    protected function getResponseType(string $modelName, Specification $spec): string
    {
        $model = $spec->schemas[$modelName] ?? null;
        $type = ($model instanceof ObjectSchema && $model->additionalProperties ? '' : 'Models.') . $this->toPascalCase($modelName);
        $generics = \array_values(\array_filter(
            $this->getGenericTypes($modelName, $spec),
            fn(string $generic): bool => $generic !== $this->toPascalCase($modelName),
        ));
        return $generics === [] ? $type : $type . '<' . \implode(', ', $generics) . '>';
    }

    public function getReturn(Operation $method, Specification $spec): string
    {
        $type = $this->getMethodType($method);
        if ($type === 'webAuth') {
            return 'void | string';
        }
        if ($type === 'location') {
            return 'string';
        }

        $models = \array_values(\array_filter(
            $this->getOperationResponseModels($method),
            static fn(string $model): bool => $model !== 'any',
        ));
        if ($models !== []) {
            return 'Promise<' . \implode(' | ', \array_map(fn(string $model): string => $this->getResponseType($model, $spec), $models)) . '>';
        }
        return 'Promise<{}>';
    }

    public function getSubSchema(Schema $property, Specification $spec, string $methodName = ''): string
    {
        $schema = $this->getSchema($property);
        $models = $this->getSchemaModels($property);

        // A union is only expressible as a member list when it is the element
        // type of an array. A bare union property stays `object`, as it was
        // before unions were resolved outside arrays at all.
        if (\count($models) > 1 && !($schema instanceof ArraySchema)) {
            return 'object';
        }

        if ($models !== []) {
            // A union names its members from outside the declaring namespace,
            // so those need qualifying; a single model is written bare.
            $qualify = \count($models) > 1;
            $types = \array_map(function (string $modelName) use ($spec, $qualify): string {
                $type = $this->toPascalCase($modelName);
                $generics = \array_values(\array_filter(
                    $this->getGenericTypes($modelName, $spec),
                    fn(string $generic): bool => $generic !== $type,
                ));
                if ($qualify) {
                    $type = 'Models.' . $type;
                }
                return $generics === [] ? $type : $type . '<' . \implode(', ', $generics) . '>';
            }, $models);
            $type = \implode(' | ', $types);
            return $schema instanceof ArraySchema
                ? (\count($types) > 1 ? '(' . $type . ')[]' : $type . '[]')
                : $type;
        }
        if ($this->getSchemaType($property) === self::TYPE_OBJECT) {
            return 'object';
        }
        return $this->getTypeName($property, $spec);
    }

    protected function getPropertyType(Schema|Parameter $value, Operation $method, Specification $spec): string
    {
        if ($this->getSchemaType($value) === self::TYPE_OBJECT) {
            $responseModel = $this->getOperationResponseModels($method)[0] ?? '';
            if ($responseModel === 'user') {
                return 'Partial<Preferences>';
            }
            if (\in_array($responseModel, ['document', 'row'], true)) {
                $generic = $this->toPascalCase($responseModel);
                $methodName = $method->method->value;
                if ($methodName === 'post') {
                    return "{$generic} extends Models.Default{$generic} ? Partial<Models.{$generic}> & Record<string, any> : Partial<Models.{$generic}> & Omit<{$generic}, keyof Models.{$generic}>";
                }
                if (\in_array($methodName, ['patch', 'put'], true)) {
                    return "{$generic} extends Models.Default{$generic} ? Partial<Models.{$generic}> & Record<string, any> : Partial<Models.{$generic}> & Partial<Omit<{$generic}, keyof Models.{$generic}>>";
                }
            }
        }
        $schema = $this->getSchema($value);
        if ($this->getSchemaModels($value) === []) {
            if ($schema instanceof ArraySchema && $this->getSchemaType($schema->items) === self::TYPE_OBJECT) {
                return 'object[]';
            }
            if ($this->getSchemaType($value) === self::TYPE_OBJECT) {
                return 'object';
            }
        }
        return $this->getTypeName($value, $spec);
    }

    /**
     * Render the parameter-overload guard exactly as Prettier would print it.
     *
     * The condition is always longer than the print width, so Prettier breaks it
     * onto one operand per line. Emitting the pre-broken form keeps the generated
     * SDK formatter-clean without a post-generation pass.
     *
     * @param array<int, string> $keys
     */
    protected function formatOverloadCondition(bool $hasRequired, array $keys): string
    {
        $indent = str_repeat(' ', 12);
        $shape = [
            'paramsOrFirst',
            "typeof paramsOrFirst === 'object'",
            '!Array.isArray(paramsOrFirst)',
        ];

        // The optional-params form nests this group one level deeper, so its
        // continuation lines and width budget shift with it.
        $groupIndent = $hasRequired ? $indent : $indent . str_repeat(' ', 4);

        if (count($keys) === 1) {
            $shape[] = $keys[0];
        } elseif (count($keys) > 1) {
            $group = '(' . implode(' || ', $keys) . ')';
            $shape[] = mb_strlen($groupIndent . $group) <= self::PRINT_WIDTH
                ? $group
                : '(' . implode(' ||' . "\n" . $groupIndent . str_repeat(' ', 4), $keys) . ')';
        }

        if (!$hasRequired) {
            // The optional-params form nests the object test inside `!paramsOrFirst || (...)`,
            // so Prettier indents its operands one level deeper.
            $nested = $indent . str_repeat(' ', 4);
            $body = implode(' &&' . "\n" . $nested, $shape);

            return '!paramsOrFirst ||' . "\n" . $indent . '(' . $body . ')';
        }

        return implode(' &&' . "\n" . $indent, $shape);
    }

    /**
     * Render one element of a rest-parameter tuple.
     *
     * Prettier strips redundant parentheses, but a conditional or function type
     * still needs them before the optional marker or the result is invalid
     * TypeScript (TS17019).
     */
    protected function formatTupleElement(string $type): string
    {
        // A union, conditional, or function type must keep its parentheses or
        // the optional marker binds to the last member instead of the whole
        // type (TS1257/TS17019).
        $needsParens = str_contains($type, ' extends ')
            || str_contains($type, '=>')
            || count($this->splitTopLevel($type, '|', trackGenerics: true)) > 1;

        return $needsParens ? '(' . $type . ')?' : $type . '?';
    }

    /**
     * Render a type alias header, breaking its generic parameters when the
     * declaration line overflows the print width.
     */
    protected function formatTypeAlias(string $name, string $generics, string $tail, int $indent): string
    {
        $pad = str_repeat(' ', $indent);
        $oneLine = $pad . 'export type ' . $name . $generics . ' = ' . $tail;

        if ($generics === '' || mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return 'export type ' . $name . $generics . ' = ' . $tail;
        }

        $inner = str_repeat(' ', $indent + 4);
        $body = '';
        foreach ($this->splitTopLevel(mb_substr($generics, 1, -1), ',', trackGenerics: true) as $argument) {
            $body .= $inner . $argument . ",\n";
        }

        return 'export type ' . $name . "<\n" . $body . $pad . '> = ' . $tail;
    }

    /**
     * Render the `apiPath` assignment with its `.replace()` chain the way
     * Prettier prints it.
     *
     * Three layouts are possible: the whole statement on one line; a single
     * call with its arguments broken out; or, for a chain of two or more
     * calls, the member chain indented under the assignment.
     *
     * @param array<int, array{0: string, 1: string}> $replacements
     */
    protected function formatApiPath(string $path, array $replacements, int $indent = 8): string
    {
        $pad = str_repeat(' ', $indent);
        $literal = "'" . $path . "'";

        $chain = '';
        foreach ($replacements as [$search, $value]) {
            $chain .= ".replace('" . $search . "', " . $value . ')';
        }

        $oneLine = $pad . 'const apiPath = ' . $literal . $chain . ';';
        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return 'const apiPath = ' . $literal . $chain . ';';
        }

        // A single call keeps the callee on the assignment line and breaks its
        // arguments; a longer chain moves the whole chain onto its own lines.
        if (count($replacements) === 1) {
            [$search, $value] = $replacements[0];
            $inner = str_repeat(' ', $indent + 4);

            if (mb_strlen($pad . 'const apiPath = ' . $literal . '.replace(') <= self::PRINT_WIDTH) {
                return 'const apiPath = ' . $literal . ".replace(\n"
                    . $inner . "'" . $search . "',\n"
                    . $inner . $value . ",\n"
                    . $pad . ');';
            }

            $deep = str_repeat(' ', $indent + 8);

            return "const apiPath =\n" . $inner . $literal . ".replace(\n"
                . $deep . "'" . $search . "',\n"
                . $deep . $value . ",\n"
                . $inner . ');';
        }

        $assignment = $pad . 'const apiPath = ' . $literal;
        if (mb_strlen($assignment) <= self::PRINT_WIDTH) {
            $callIndent = str_repeat(' ', $indent + 4);
            $out = 'const apiPath = ' . $literal;
        } else {
            $callIndent = str_repeat(' ', $indent + 8);
            $out = "const apiPath =\n" . str_repeat(' ', $indent + 4) . $literal;
        }

        $last = array_key_last($replacements);
        foreach ($replacements as $key => [$search, $value]) {
            $call = ".replace('" . $search . "', " . $value . ')';
            // Only the final call in the chain carries the statement's semicolon.
            $terminator = $key === $last ? ';' : '';

            if (mb_strlen($callIndent . $call . $terminator) <= self::PRINT_WIDTH) {
                $out .= "\n" . $callIndent . $call;
                continue;
            }

            $argIndent = $callIndent . '    ';
            $out .= "\n" . $callIndent . ".replace(\n"
                . $argIndent . "'" . $search . "',\n"
                . $argIndent . $value . ",\n"
                . $callIndent . ')';
        }

        return $out . ';';
    }

    /**
     * Render `<prefix>{ a: A; b: B }<suffix>` the way Prettier prints it,
     * expanding the object across lines only when the statement overflows.
     */
    protected function formatObjectStatement(string $prefix, string $body, string $suffix, int $indent): string
    {
        $pad = str_repeat(' ', $indent);
        $oneLine = $pad . $prefix . '{ ' . $body . ' }' . $suffix;

        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return $prefix . '{ ' . $body . ' }' . $suffix;
        }

        $inner = str_repeat(' ', $indent + 4);
        $lines = '';
        foreach ($this->splitTopLevel($body, ';', trackGenerics: true) as $member) {
            $lines .= $inner . $this->wrapConditionalType($member, $indent + 4) . ";\n";
        }

        return $prefix . "{\n" . $lines . $pad . '}' . $suffix;
    }

    /**
     * Render a single-argument call, breaking it when it overflows the width.
     */
    protected function formatCall(string $callee, string $argument, string $suffix, int $indent): string
    {
        $pad = str_repeat(' ', $indent);
        $oneLine = $pad . $callee . '(' . $argument . ')' . $suffix;

        if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
            return $callee . '(' . $argument . ')' . $suffix;
        }

        return $callee . "(\n" . str_repeat(' ', $indent + 4) . $argument . ",\n" . $pad . ')' . $suffix;
    }

    /**
     * Break a union-typed parameter across lines when it overflows the print
     * width, matching Prettier's leading-pipe union layout.
     */
    protected function wrapUnionParameter(string $param, int $indent): string
    {
        if (mb_strlen(str_repeat(' ', $indent) . $param . ',') <= self::PRINT_WIDTH) {
            return $param;
        }

        // A rest element carrying a tuple type breaks one element per line, but
        // only when the tuple itself does not fit on its own line.
        if (preg_match('/^(\.\.\.\w+): \[(.+)\]$/', $param, $tuple) === 1) {
            if (mb_strlen(str_repeat(' ', $indent) . $param) <= self::PRINT_WIDTH) {
                return $param;
            }

            $inner = str_repeat(' ', $indent + 4);
            $body = '';
            foreach ($this->splitTopLevel($tuple[2], ',', trackGenerics: true) as $element) {
                $body .= $inner . $this->wrapConditionalType($element, $indent + 4) . ",\n";
            }

            return $tuple[1] . ": [\n" . $body . str_repeat(' ', $indent) . ']';
        }

        $colon = mb_strpos($param, ': ');
        if ($colon === false) {
            return $param;
        }

        $label = mb_substr($param, 0, $colon);
        $type = mb_substr($param, $colon + 2);
        $options = $this->splitTopLevel($type, '|', trackGenerics: true);

        if (count($options) < 2) {
            return $param;
        }

        $inner = str_repeat(' ', $indent + 4);

        // Prettier first tries the union on its own line under the label.
        if (mb_strlen($inner . $type . ',') <= self::PRINT_WIDTH) {
            return $label . ":\n" . $inner . $type;
        }

        $rendered = [];
        foreach ($options as $option) {
            $rendered[] = $this->expandTypeOption($option, $indent + 4);
        }

        return $label . ":\n" . $inner . '| ' . implode("\n" . $inner . '| ', $rendered);
    }

    /**
     * Break an intersection type across lines when the branch overflows.
     * Prettier hangs the continuation past the conditional's `? ` / `: ` marker.
     */
    protected function wrapIntersection(string $type, int $indent): string
    {
        if (mb_strlen(str_repeat(' ', $indent) . $type . ';') <= self::PRINT_WIDTH) {
            return $type;
        }

        $parts = $this->splitTopLevel($type, '&', trackGenerics: true);
        if (count($parts) < 2) {
            return $type;
        }

        return implode(" &\n" . str_repeat(' ', $indent + 4), $parts);
    }

    /**
     * Break a conditional type across lines the way Prettier prints it.
     */
    protected function wrapConditionalType(string $member, int $indent): string
    {
        if (mb_strlen(str_repeat(' ', $indent) . $member . ';') <= self::PRINT_WIDTH) {
            return $member;
        }

        if (preg_match('/^(.*?) extends (.*?) \? (.*) : (.*)$/', $member, $match) !== 1) {
            return $member;
        }

        $inner = str_repeat(' ', $indent + 4);

        return $match[1] . ' extends ' . $match[2] . "\n"
            . $inner . '? ' . $this->wrapIntersection($match[3], $indent + 6) . "\n"
            . $inner . ': ' . $this->wrapIntersection($match[4], $indent + 6);
    }

    /**
     * Expand a `label: { ... }` member whose inline object type overflows.
     */
    protected function expandObjectMember(string $member, int $indent): string
    {
        if (preg_match('/^([^:]+): \\{ (.+) \\}$/', $member, $match) !== 1) {
            return $member;
        }

        $inner = str_repeat(' ', $indent + 4);
        $body = '';
        foreach ($this->splitTopLevel($match[2], ';', trackGenerics: true) as $nested) {
            $body .= $inner . $nested . ";\n";
        }

        return $match[1] . ": {\n" . $body . str_repeat(' ', $indent) . '}';
    }

    /**
     * Expand a single union option when it overflows on its own line.
     * Prettier aligns the expanded members past the `| ` marker.
     */
    protected function expandTypeOption(string $option, int $indent): string
    {
        if (mb_strlen(str_repeat(' ', $indent) . '| ' . $option) <= self::PRINT_WIDTH) {
            return $option;
        }

        if (preg_match('/^\{ (.+) \}$/', $option, $match) !== 1) {
            return $option;
        }

        $inner = str_repeat(' ', $indent + 6);
        $body = '';
        foreach ($this->splitTopLevel($match[1], ';', trackGenerics: true) as $member) {
            $member = $this->wrapConditionalType($member, $indent + 6);

            // A member that is itself an object type may still overflow, in
            // which case Prettier expands it one level deeper as well.
            if (mb_strlen($inner . $member . ';') > self::PRINT_WIDTH) {
                $member = $this->expandObjectMember($member, $indent + 6);
            }

            $body .= $inner . $member . ";\n";
        }

        return "{\n" . $body . str_repeat(' ', $indent + 2) . '}';
    }

    /**
     * Render a model property, breaking a parenthesised union array type across
     * lines when the declaration overflows the print width.
     */
    protected function formatModelProperty(string $label, string $type, int $indent): string
    {
        $pad = str_repeat(' ', $indent);
        if (mb_strlen($pad . $label . ': ' . $type . ';') <= self::PRINT_WIDTH) {
            return $label . ': ' . $type . ';';
        }

        if (preg_match('/^\((.+)\)\[\]$/', $type, $match) !== 1) {
            return $label . ': ' . $type . ';';
        }

        $options = $this->splitTopLevel($match[1], '|', trackGenerics: true);
        if (count($options) < 2) {
            return $label . ': ' . $type . ';';
        }

        $inner = $pad . str_repeat(' ', 4);
        $joined = implode(' | ', $options);

        // Prettier only falls back to the leading-pipe form when the union does
        // not fit on a single indented line inside the parentheses.
        if (mb_strlen($inner . $joined) <= self::PRINT_WIDTH) {
            return $label . ": (\n" . $inner . $joined . "\n" . $pad . ')[];';
        }

        return $label . ": (\n" . $inner . '| '
            . implode("\n" . $inner . '| ', $options) . "\n" . $pad . ')[];';
    }

    /**
     * Break a union return type across lines when the closing `): Type` line
     * overflows, matching Prettier's leading-pipe layout inside the generic.
     */
    protected function wrapReturnType(string $return, int $indent, string $suffix, string $prefix = '): '): string
    {
        $pad = str_repeat(' ', $indent);
        if (mb_strlen($pad . $prefix . $return . $suffix) <= self::PRINT_WIDTH) {
            return $return;
        }

        // Only a generic wrapping a top-level union can be broken this way.
        if (preg_match('/^([\w.]+)<(.+)>$/', $return, $generic) !== 1) {
            return $return;
        }

        $options = $this->splitTopLevel($generic[2], '|', trackGenerics: true);
        if (count($options) < 2) {
            return $return;
        }

        $inner = $pad . str_repeat(' ', 4);
        $joined = implode(' | ', $options);

        // Prettier only falls back to the leading-pipe form when the union does
        // not fit on a single indented line inside the generic.
        if (mb_strlen($inner . $joined) <= self::PRINT_WIDTH) {
            return $generic[1] . "<\n" . $inner . $joined . "\n" . $pad . '>';
        }

        return $generic[1] . "<\n" . $inner . '| '
            . implode("\n" . $inner . '| ', $options) . "\n" . $pad . '>';
    }

    /**
     * Render a method signature the way Prettier would print it.
     *
     * Prettier keeps a signature on one line while it fits inside the print
     * width and otherwise breaks it, so the templates cannot know the final
     * shape without measuring. Rendering it here keeps generated SDKs
     * formatter-clean without a post-generation pass.
     *
     * @param array<int, string> $params Rendered parameters, without separators.
     */
    protected function formatSignature(
        string $name,
        array $params,
        string $return,
        int $indent = 4,
        string $suffix = ';',
        int $prefixWidth = 0,
    ): string {
        $pad = str_repeat(' ', $indent);
        $oneLine = $pad . $name . '(' . implode(', ', $params) . '): ' . $return . $suffix;

        if (mb_strlen($oneLine) + $prefixWidth <= self::PRINT_WIDTH && !str_contains($oneLine, "\n")) {
            return ltrim($oneLine);
        }

        // When the name plus its generic parameters alone overflow, Prettier
        // breaks the generics first and re-measures the rest of the signature.
        $generics = mb_strpos($name, '<');
        $hugged = count($params) === 1
            && preg_match('/^(\w+\??): \{ (.+) \}$/', $params[0], $hugMatch) === 1;
        // The first physical line is `name(param: {` when the object is hugged,
        // and `name(` otherwise; generics break when that line overflows.
        $headLine = $params === []
            ? $oneLine
            : $pad . $name . '(' . ($hugged ? $hugMatch[1] . ': {' : '');

        if (
            $generics !== false
            && mb_strlen($headLine) > self::PRINT_WIDTH
            && mb_strlen($pad . mb_substr($name, 0, $generics) . '<') <= self::PRINT_WIDTH
        ) {
            $bare = mb_substr($name, 0, $generics);
            $inner = str_repeat(' ', $indent + 4);
            $body = '';
            foreach ($this->splitTopLevel(mb_substr($name, $generics + 1, -1), ',', trackGenerics: true) as $argument) {
                $body .= $inner . $argument . ",\n";
            }

            $head = $bare . "<\n" . $body . $pad . '>';
            $tail = '(' . implode(', ', $params) . '): ' . $return . $suffix;

            if (
                mb_strlen($pad . '>' . $tail) <= self::PRINT_WIDTH
                && !str_contains($tail, "\n")
            ) {
                return $head . $tail;
            }

            // The `>` closing the generics occupies the first column of the
            // tail line, so the recursion measures one character wider.
            return $head . $this->formatSignature('', $params, $return, $indent, $suffix, 1);
        }

        $inner = str_repeat(' ', $indent + 4);

        // A lone object-typed parameter is "hugged": Prettier expands the object's
        // members in place instead of breaking the parameter list itself.
        if (count($params) === 1 && preg_match('/^(\w+\??): \{ (.+) \}$/', $params[0], $match) === 1) {
            $members = $this->splitTopLevel($match[2], ';', trackGenerics: true);
            $body = '';
            foreach ($members as $member) {
                $body .= $inner . $this->wrapConditionalType($member, $indent + 4) . ";\n";
            }

            $hugged = $this->wrapReturnType($return, $indent, $suffix, '}): ');

            return $name . '(' . $match[1] . ": {\n" . $body . $pad . '}): ' . $hugged . $suffix;
        }

        $broken = $name . "(\n";
        $last = array_key_last($params);
        foreach ($params as $key => $param) {
            // A rest element never takes a trailing comma.
            $comma = ($key === $last && str_starts_with(ltrim((string) $param), '...')) ? '' : ',';
            $rendered = $this->wrapUnionParameter($param, $indent + 4);
            if ($rendered === $param) {
                $rendered = $this->wrapConditionalType($param, $indent + 4);
            }

            $broken .= $inner . $rendered . $comma . "\n";
        }

        return $broken . $pad . '): ' . $this->wrapReturnType($return, $indent, $suffix) . $suffix;
    }

    #[Override]
    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('getPropertyType', fn(Schema|Parameter $value, Operation|Specification $context, ?Specification $spec = null): string => $context instanceof Operation
                ? $this->getPropertyType($value, $context, $spec ?? throw new InvalidArgumentException('Specification is required'))
                : $this->getTypeName($value, $context)),
            new TwigFilter('getSubSchema', fn(Schema $property, Specification $spec, string $methodName = ''): string => $this->getSubSchema($property, $spec, $methodName)),
            new TwigFilter('getGenerics', fn(string $model, Specification $spec, bool $skipAdditional = false): string => $this->getGenerics($model, $spec, $skipAdditional)),
            new TwigFilter('getReturn', fn(Operation $method, Specification $spec): string => $this->getReturn($method, $spec)),
            new TwigFilter('tsAssignment', function (string $lhs, string $type, int $indent = 16): string {
                $oneLine = str_repeat(' ', $indent) . $lhs . ' ' . $type . ',';

                if (mb_strlen($oneLine) <= self::PRINT_WIDTH) {
                    return $lhs . ' ' . $type;
                }

                // An inline object type expands its members; anything else
                // falls back to the conditional-type layout.
                if (preg_match('/^\\{ (.+) \\}$/', $type, $match) === 1) {
                    $inner = str_repeat(' ', $indent + 4);
                    $body = '';
                    foreach ($this->splitTopLevel($match[1], ';', trackGenerics: true) as $member) {
                        $body .= $inner . $member . ";\n";
                    }

                    return $lhs . " {\n" . $body . str_repeat(' ', $indent) . '}';
                }

                return $lhs . ' ' . $this->wrapConditionalType($type, $indent);
            }, ['is_safe' => ['html']]),
            new TwigFilter('tsModelProperty', fn(string $label, string $type, int $indent = 8): string => $this->formatModelProperty($label, $type, $indent), ['is_safe' => ['html']]),
            new TwigFilter('tsTupleElement', fn(string $type): string => $this->formatTupleElement($type), ['is_safe' => ['html']]),
            new TwigFilter('tsTypeAlias', fn(string $name, string $generics, string $tail, int $indent = 4): string => $this->formatTypeAlias($name, $generics, $tail, $indent), ['is_safe' => ['html']]),
            new TwigFilter('tsApiPath', fn(string $path, array $replacements, int $indent = 8): string => $this->formatApiPath($path, $replacements, $indent), ['is_safe' => ['html']]),
            new TwigFilter('tsObjectStatement', fn(string $prefix, string $body, string $suffix = ';', int $indent = 8): string => $this->formatObjectStatement($prefix, trim($body), $suffix, $indent), ['is_safe' => ['html']]),
            new TwigFilter('tsCall', fn(string $callee, string $argument, string $suffix = ';', int $indent = 12): string => $this->formatCall($callee, trim($argument), $suffix, $indent), ['is_safe' => ['html']]),
            new TwigFilter('tsSignature', fn(string $name, array $params, string $return, int $indent = 4, string $suffix = ';'): string => $this->formatSignature($name, array_values(array_filter(array_map(trim(...), $params), fn(string $p): bool => $p !== '')), $return, $indent, $suffix), ['is_safe' => ['html']]),
            new TwigFilter('getOverloadCondition', function (Operation $method, Specification $spec): string {
                $params = $this->getOperationParameters($method);

                $hasRequired = false;
                foreach ($params as $param) {
                    if ($param->required) {
                        $hasRequired = true;
                        break;
                    }
                }

                $firstParamType = $this->getPropertyType($params[0], $method, $spec);
                $isPrimitive = str_starts_with($firstParamType, 'string')
                    || str_starts_with($firstParamType, 'number')
                    || str_starts_with($firstParamType, 'boolean');

                $keys = [];
                if (!$isPrimitive) {
                    foreach ($params as $param) {
                        $name = $this->escapeKeyword($this->toCamelCase($param->name));
                        $keys[] = "'" . $name . "' in paramsOrFirst";
                    }

                    if (isset($method->requestBody?->content['multipart/form-data'])) {
                        $keys[] = "'onProgress' in paramsOrFirst";
                    }
                }

                return $this->formatOverloadCondition($hasRequired, $keys);
            }, ['is_safe' => ['html']]),
        ]);
    }
}
