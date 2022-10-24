<?php

namespace Appwrite\SDK\Language;

class Node extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'NodeJS';
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
    public function getTypeName(array $parameter): string
    {
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return 'number';
            case self::TYPE_ARRAY:
                if (!empty($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'string[]';
            case self::TYPE_FILE:
                return 'InputFile';
        }

        return $parameter['type'];
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'index.js',
                'template'      => 'node/index.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'index.d.ts',
                'template'      => 'node/index.d.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => 'node/lib/client.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/permission.js',
                'template'      => 'node/lib/permission.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/role.js',
                'template'      => 'node/lib/role.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/id.js',
                'template'      => 'node/lib/id.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/query.js',
                'template'      => 'node/lib/query.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/inputFile.js',
                'template'      => 'node/lib/inputFile.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.js',
                'template'      => 'node/lib/service.js.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.js',
                'template'      => 'node/lib/services/service.js.twig',
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
                'scope'         => 'default',
                'destination'   => 'lib/exception.js',
                'template'      => 'node/lib/exception.js.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'node/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'node/.travis.yml.twig',
            ],
        ];
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

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
                    $output .= "InputFile.fromPath('/path/to/file.png', 'file.png')";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile.fromPath('/path/to/file.png', 'file.png')";
                    break;
            }
        }

        return $output;
    }
}
