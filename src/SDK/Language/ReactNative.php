<?php

declare(strict_types=1);

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Twig\TwigFilter;

class ReactNative extends Web
{
    #[Override]
    protected function getFileExample(): string
    {
        return 'InputFile.fromPath(\'/path/to/file\', \'filename\')';
    }

    #[Override]
    public function getName(): string
    {
        return 'ReactNative';
    }

    #[Override]
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/index.ts',
                'template'      => 'react-native/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'react-native/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/react-native-shim.d.ts',
                'template'      => 'react-native/src/react-native-shim.d.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.ts',
                'template'      => 'react-native/src/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'react-native/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/realtime.ts',
                'template'      => 'web/src/services/realtime.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'react-native/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'react-native/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'react-native/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'react-native/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/channel.ts',
                'template'      => 'react-native/src/channel.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'react-native/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'react-native/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'react-native/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'react-native/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'react-native/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'react-native/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'react-native/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/react-native/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.mjs',
                'template'      => '/react-native/rollup.config.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/cjs/package.json',
                'template'      => '/react-native/dist/cjs/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/esm/package.json',
                'template'      => '/react-native/dist/esm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'react-native/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.title | caseKebab }}.ts',
                'template'      => 'react-native/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => 'react-native/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.npmrc',
                'template'      => 'react-native/.npmrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierrc',
                'template'      => 'react-native/.prettierrc',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.prettierignore',
                'template'      => 'react-native/.prettierignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'eslint.config.mjs',
                'template'      => 'react-native/eslint.config.mjs',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'react-native/package-lock.json.twig',
            ],
        ];
    }

    #[Override]
    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        if ($this->getSchemaType($parameter) === self::TYPE_FILE) {
            return '{ name: string; type: string; size: number; uri: string }';
        }

        return parent::getTypeName($parameter, $spec);
    }

    #[Override]
    public function getReturn(Operation $method, Specification $spec): string
    {
        return match ($method->extensions['x-appwrite']['type'] ?? '') {
            'webAuth' => 'void | URL',
            'location' => 'Promise<ArrayBuffer>',
            default => parent::getReturn($method, $spec),
        };
    }
}
