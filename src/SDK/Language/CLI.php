<?php

namespace Appwrite\SDK\Language;

class CLI extends PHP {

    /**
     * @return string
     */
    public function getName()
    {
        return 'CLI';
    }

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param)
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return "''";
        }

        $output = '';

        if(empty($default) && $default !== 0 && $default !== false) {
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
                case self::TYPE_OBJECT:
                    $output .= '[]';
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $default;
                    break;
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToAssoc(json_decode($default, true));
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

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/cli/README.md.twig',
                'minify'        => false,
                //'block'         => 'default',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/cli/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/cli/LICENSE.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'composer.json',
                'template'      => '/cli/composer.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/cli/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Client.php',
                'template'      => '/cli/src/Client.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Dockerfile',
                'template'      => '/cli/Dockerfile.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Parser.php',
                'template'      => '/cli/src/Parser.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/bin/{{service.name}}',
                'template'      => '/cli/bin/service.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/bin/client',
                'template'      => '/cli/bin/client.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/bin/help',
                'template'      => '/cli/bin/help.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/bin/init',
                'template'      => '/cli/bin/init.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.php',
                'template'      => '/cli/src/Services/Service.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Services/Client.php',
                'template'      => '/cli/src/Services/Client.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Services/Help.php',
                'template'      => '/cli/src/Services/Help.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Init.php',
                'template'      => '/cli/src/Init.php.twig',
                'minify'        => false,
            ],
        ];
    }

}
