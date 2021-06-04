<?php

namespace Appwrite\SDK\Language;

class Node extends JS
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'NodeJS';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'index.js',
                'template'      => 'node/index.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'index.d.ts',
                'template'      => 'node/index.d.ts.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => 'node/lib/client.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.js',
                'template'      => 'node/lib/service.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.js',
                'template'      => 'node/lib/services/service.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'node/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'node/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'node/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'node/package.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/exception.js',
                'template'      => 'node/lib/exception.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'node/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'node/.travis.yml.twig',
                'minify'        => false,
            ],
        ];
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param)
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
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
                    $output .= "fs.createReadStream(__dirname + '/file.png'))";
                    break;
            }
        }
        else {
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
                    $output .= "fs.createReadStream(__dirname + '/file.png'))";
                    break;
            }
        }

        return $output;
    }
}
