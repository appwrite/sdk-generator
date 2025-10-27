<?php

namespace Appwrite\SDK\Language;

class Deno extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Deno';
    }

    /**
     * @return array
     */
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
                'destination'   => 'test/query.test.ts',
                'template'      => 'deno/test/query.test.ts.twig',
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
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'deno/docs/example.md.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'deno/src/enums/enum.ts.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (isset($parameter['items'])) {
            $parameter['array'] = $parameter['items'];
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? $this->getTypeName($parameter['array']) . '[]'
                : 'any[]',
            self::TYPE_OBJECT => 'object',
            default => $parameter['type']
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

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
            : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getPermissionExample(string $example): string
    {
        $permissions = [];
        foreach ($this->extractPermissionParts($example) as $permission) {
            $args = [];
            if ($permission['id'] !== null) {
                $args[] = "'" . $permission['id'] . "'";
            }
            if ($permission['innerRole'] !== null) {
                $args[] = "'" . $permission['innerRole'] . "'";
            }
            $argsString = implode(', ', $args);
            $permissions[] = 'Permission.' . $permission['action'] . '(Role.' . $permission['role'] . '(' . $argsString . '))';
        }
        return '[' . implode(', ', $permissions) . ']';
    }
}
