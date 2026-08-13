<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;

class Deno extends JS
{
    public function getName(): string
    {
        return 'Deno';
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
                'destination'   => 'mod.ts',
                'template'      => 'deno/mod.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'deno/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'deno/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/permission.test.ts',
                'template'      => 'deno/test/permission.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'deno/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/role.test.ts',
                'template'      => 'deno/test/role.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'deno/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/id.test.ts',
                'template'      => 'deno/test/id.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'deno/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'deno/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/query.test.ts',
                'template'      => 'deno/test/query.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/operator.test.ts',
                'template'      => 'deno/test/operator.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/inputFile.ts',
                'template'      => 'deno/src/inputFile.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/service.ts',
                'template'      => 'deno/src/service.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/models.d.ts',
                'template'      => 'deno/src/models.d.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/exception.ts',
                'template'      => 'deno/src/exception.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'deno/src/services/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/test/services/{{service.name | caseKebab}}.test.ts',
                'template'      => 'deno/test/services/service.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'deno/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'deno/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'deno/LICENSE.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'deno/docs/example.md.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.title | caseKebab }}.ts',
                'template'      => 'deno/src/enums/enum.ts.twig',
            ],
        ];
    }

    #[Override]
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
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_ARRAY => $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . '[]',
            self::TYPE_OBJECT => 'object',
            default => 'unknown',
        };
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'InputFile.fromPath(\'/path/to/file.png\', \'file.png\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_FILE => 'InputFile.fromPath(\'/path/to/file.png\', \'file.png\')',
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode((string) $example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }
}
