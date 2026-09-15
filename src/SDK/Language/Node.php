<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Twig\TwigFilter;

class Node extends Web
{
    #[Override]
    protected function getFileExample(): string
    {
        return 'InputFile.fromPath(\'/path/to/file\', \'filename\')';
    }

    #[Override]
    public function getName(): string
    {
        return 'NodeJS';
    }

    #[Override]
    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    #[Override]
    public function getStringQuote(): string
    {
        return "'";
    }

    #[Override]
    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    #[Override]
    protected function getPermissionPrefix(): string
    {
        return 'sdk.';
    }

    #[Override]
    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        if ($schema instanceof ArraySchema && $this->getSchemaType($schema->items) === self::TYPE_FILE) {
            return '(File | InputFile)[]';
        }
        if ($this->getSchemaType($parameter) === self::TYPE_FILE) {
            return 'File | InputFile';
        }
        return parent::getTypeName($parameter, $spec);
    }

    #[Override]
    public function getReturn(Operation $method, Specification $spec): string
    {
        return match ($this->getMethodType($method, $spec)) {
            'webAuth' => 'Promise<string>',
            'location' => 'Promise<ArrayBuffer>',
            default => parent::getReturn($method, $spec),
        };
    }

    /**
     * Check if service has any file parameters
     */
    public function hasFileParam(array $methods): bool
    {
        foreach ($methods as $method) {
            foreach ($this->getOperationParameters($method) as $parameter) {
                if ($this->getSchemaType($parameter) === self::TYPE_FILE) {
                    return true;
                }
            }
        }
        return false;
    }

    #[Override]
    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('hasFileParam', fn(array $methods): bool => $this->hasFileParam($methods)),
        ]);
    }

    #[Override]
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/index.ts',
                'template'      => 'node/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'node/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/inputFile.ts',
                'template'      => 'node/src/inputFile.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'node/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'web/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/client.test.js',
                'template'      => 'node/test/client.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/permission.test.js',
                'template'      => 'node/test/permission.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'web/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/role.test.js',
                'template'      => 'node/test/role.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'web/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/id.test.js',
                'template'      => 'node/test/id.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'web/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/query.test.js',
                'template'      => 'node/test/query.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'web/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/operator.test.js',
                'template'      => 'node/test/operator.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'node/src/operator.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'test/services/{{service.name | caseDash}}.test.js',
                'template'      => 'node/test/services/service.test.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'node/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'node/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'node/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'node/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'node/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => 'node/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsup.config.ts',
                'template'      => 'node/tsup.config.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'node/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.title | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => 'src/models/{{ requestModelName | caseKebab }}.ts',
                'template'      => 'node/src/models/requestModel.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => 'node/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.npmrc',
                'template'      => 'node/.npmrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierrc',
                'template'      => 'node/.prettierrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierignore',
                'template'      => 'node/.prettierignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'eslint.config.mjs',
                'template'      => 'node/eslint.config.mjs',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'node/package-lock.json.twig',
            ],
        ];
    }
}
