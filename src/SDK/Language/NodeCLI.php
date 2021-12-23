<?php

namespace Appwrite\SDK\Language;

class NodeCLI extends JS
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'NodeCLI';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'node-cli/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'node-cli/package.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => 'node-cli/LICENSE.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'install.sh',
                'template'      => 'node-cli/install.sh',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'index.js',
                'template'      => 'node-cli/index.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/npm-publish.yml',
                'template'      => 'node-cli/.github/workflows/npm-publish.yml',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/sdks.js',
                'template'      => 'node-cli/lib/sdks.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/questions.js',
                'template'      => 'node-cli/lib/questions.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/parser.js',
                'template'      => 'node-cli/lib/parser.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/exception.js',
                'template'      => 'node-cli/lib/exception.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/config.js',
                'template'      => 'node-cli/lib/config.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => 'node-cli/lib/client.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/init.js',
                'template'      => 'node-cli/lib/commands/init.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/deploy.js',
                'template'      => 'node-cli/lib/commands/deploy.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/commands/{{service.name | caseDash}}.js',
                'template'      => 'node-cli/lib/commands/command.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/auth.js',
                'template'      => 'node-cli/lib/commands/auth.js.twig',
                'minify'        => false,
            ]
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
                    $output .= "fs.createReadStream(__dirname + '/file.png')";
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
                    $output .= "fs.createReadStream(__dirname + '/file.png')";
                    break;
            }
        }

        return $output;
    }
}
