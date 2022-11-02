<?php

namespace Appwrite\SDK\Language;

class CLI extends Node
{
    /**
     * @var array
     */
    protected $params = [
        'npmPackage' => 'packageName',
        'executableName' => 'executable',
        'logo' => '',
        'logoUnescaped' => '',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setExecutableName(string $name): self
    {
        $this->setParam('executableName', $name);

        return $this;
    }

    /**
     * @param string $logo
     * @return $this
     */
    public function setLogo(string $logo): self
    {
        $this->setParam('logo', $logo);

        return $this;
    }

    /**
     * @param string $logo
     * @return $this
     */
    public function setLogoUnescaped(string $logo): self
    {
        $this->setParam('logoUnescaped', $logo);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'cli';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'cli/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'cli/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => 'cli/LICENSE.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'install.sh',
                'template'      => 'cli/install.sh.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'install.ps1',
                'template'      => 'cli/install.ps1.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'index.js',
                'template'      => 'cli/index.js.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'cli/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.gitignore',
                'template'      => 'cli/.gitignore',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'Formula/{{ language.params.executableName }}.rb',
                'template'      => 'cli/Formula/formula.rb.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/npm-publish.yml',
                'template'      => 'cli/.github/workflows/npm-publish.yml',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/sdks.js',
                'template'      => 'cli/lib/sdks.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/questions.js',
                'template'      => 'cli/lib/questions.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/parser.js',
                'template'      => 'cli/lib/parser.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/exception.js',
                'template'      => 'cli/lib/exception.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/config.js',
                'template'      => 'cli/lib/config.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => 'cli/lib/client.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/utils.js',
                'template'      => 'cli/lib/utils.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/init.js',
                'template'      => 'cli/lib/commands/init.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/deploy.js',
                'template'      => 'cli/lib/commands/deploy.js.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/commands/{{service.name | caseDash}}.js',
                'template'      => 'cli/lib/commands/command.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/generic.js',
                'template'      => 'cli/lib/commands/generic.js.twig',
            ]
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
                    $output .= 'one two three';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '\'{ "key": "value" }\'';
                    break;
                case self::TYPE_FILE:
                    $output .= "'path/to/file.png'";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_ARRAY:
                    if (str_contains($example, '[') && str_contains($example, ']')) {
                        $trimmed = substr($example, 1, -1);
                        $split = explode(',', $trimmed);
                        $output .= implode(' ', $split);
                    } else {
                        $output .= $example;
                    }
                    break;
                case self::TYPE_OBJECT:
                    $output .= '\'{ "key": "value" }\'';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "{$example}";
                    break;
                case self::TYPE_FILE:
                    $output .= "'path/to/file.png'";
                    break;
            }
        }

        return $output;
    }
}
