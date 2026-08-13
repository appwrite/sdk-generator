<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Ruby extends Language
{
    #[Override]
    protected $params = [
        'gemPackage' => 'gemName',
    ];

    /**
     * @return $this
     */
    public function setGemPackage(string $name): self
    {
        $this->setParam('gemPackage', $name);

        return $this;
    }

    public function getName(): string
    {
        return 'Ruby';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            'BEGIN',
            'END',
            'alias',
            'and',
            'begin',
            'break',
            'case',
            'class',
            'def',
            'defined?',
            'do',
            'else',
            'module',
            'next',
            'nil',
            'not',
            'or',
            'redo',
            'rescue',
            'retry',
            'return',
            'self',
            'super',
            'then',
            'elsif',
            'end',
            'false',
            'ensure',
            'for',
            'if',
            'true',
            'undef',
            'unless',
            'until',
            'when',
            'while',
            'path'
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [];
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
                'destination'   => 'README.md',
                'template'      => 'ruby/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'ruby/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'ruby/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Gemfile',
                'template'      => 'ruby/Gemfile.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseDash }}.gemspec',
                'template'      => 'ruby/gemspec.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}.rb',
                'template'      => 'ruby/lib/container.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/client.rb',
                'template'      => 'ruby/lib/container/client.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/permission.rb',
                'template'      => 'ruby/lib/container/permission.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/role.rb',
                'template'      => 'ruby/lib/container/role.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/id.rb',
                'template'      => 'ruby/lib/container/id.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/query.rb',
                'template'      => 'ruby/lib/container/query.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/operator.rb',
                'template'      => 'ruby/lib/container/operator.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/service.rb',
                'template'      => 'ruby/lib/container/service.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/input_file.rb',
                'template'      => 'ruby/lib/container/input_file.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.info.title | caseDash }}/exception.rb',
                'template'      => 'ruby/lib/container/exception.rb.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/{{ spec.info.title | caseDash}}/services/{{service.name | caseSnake}}.rb',
                'template'      => 'ruby/lib/container/services/service.rb.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'ruby/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'ruby/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/{{ spec.info.title | caseDash }}/models/{{ definitionName | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/models/model.rb.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/lib/{{ spec.info.title | caseDash }}/models/{{ requestModelName | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/models/request_model.rb.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/{{ spec.info.title | caseSnake}}/enums/{{ enum.title | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/enums/enum.rb.twig',
            ],
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $model = $this->getSchemaModel($parameter);
        if ($model !== null && !($schema instanceof ArraySchema)) {
            return $this->toPascalCase($model);
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'Integer',
            self::TYPE_NUMBER => 'Float',
            self::TYPE_STRING => 'String',
            self::TYPE_ARRAY => 'Array',
            self::TYPE_OBJECT => 'Hash',
            self::TYPE_BOOLEAN => '',
            default => 'String',
        };
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type       = $this->getSchemaType($param);
        $default    = $this->getSchemaDefault($param);
        $required   = ($param instanceof Parameter && $param->required);

        if ($required) {
            return ':';
        }

        $output = ': ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'nill';
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

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
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
                case self::TYPE_FILE:
                    $output .= "InputFile.from_path('dir/file.png')";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    $output .= $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example;
                    break;
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToHash(json_decode((string) $example, true));
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile.from_path('dir/file.png')";
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To Ruby Native Hash
     *
     * @var $data array
     */
    protected function jsonToHash(array $data, int $indent = 0): string
    {
        if ($data === []) {
            return '{}';
        }

        $output = "{\n";
        $indentStr = str_repeat('  ', $indent + 4);
        $keys = array_keys($data);

        foreach ($data as $key => $node) {
            if (is_array($node)) {
                $value = $this->jsonToHash($node, $indent + 1);
            } elseif (is_bool($node)) {
                $value = $node ? 'true' : 'false';
            } elseif (is_string($node)) {
                $value = '"' . $node . '"';
            } else {
                $value = $node;
            }

            $output .= $indentStr . '"' . $key . '" => ' . $value;

            // Add comma if not the last item
            if ($key !== end($keys)) {
                $output .= ',';
            }

            $output .= "\n";
        }

        return $output . (str_repeat('  ', $indent + 2) . '}');
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toUpperSnakeCase($value)),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $enumSchema->extensions['x-enum-keys'] ?? [];
                $enumName = $this->toPascalCase($enumSchema->extensions['x-enum-name'] ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        return $this->toUpperSnakeCase($enumKeys[$index]);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        return $this->toUpperSnakeCase($enumValues[$index]);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    return $this->toUpperSnakeCase((string)$fallback);
                };

                if ($isArray) {
                    $values = [];
                    if (\is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (\is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (\is_array($example)) {
                        $values = $example;
                    }

                    if ($values === []) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(fn($value): string => $enumName . '::' . $resolveKey($value), $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '::' . $resolveKey($value);
            }),
        ];
    }
}
