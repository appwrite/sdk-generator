<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;
use Twig\TwigFunction;

class CLI extends Node
{
    /**
     * List of functions to ignore for console preview.
     * @var array
     */
    private $consoleIgnoreFunctions = [
        'listidentities',
        'listmfafactors',
        'getprefs',
        'getsession',
        'getattribute',
        'listdocumentlogs',
        'getindex',
        'listcollectionlogs',
        'getcollectionusage',
        'listlogs',
        'listruntimes',
        'getusage',
        'getusage',
        'listvariables',
        'getvariable',
        'listproviderlogs',
        'listsubscriberlogs',
        'getsubscriber',
        'listtopiclogs',
        'getemailtemplate',
        'getsmstemplate',
        'getfiledownload',
        'getfilepreview',
        'getfileview',
        'getusage',
        'listlogs',
        'getprefs',
        'getusage',
        'listlogs',
        'getmembership',
        'listmemberships',
        'listmfafactors',
        'getmfarecoverycodes',
        'getprefs',
        'listtargets',
        'gettarget',
    ];

    /**
     * List of SDK services to ignore for console preview.
     * @var array
     */
    private $consoleIgnoreServices = [
        'health',
        'migrations',
        'locale',
        'avatars',
        'project',
        'proxy',
        'vcs'
    ];
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
                'destination'   => 'scoop/appwrite.json',
                'template'      => 'cli/scoop/appwrite.json.twig',
                'minify'        => false,
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
                'destination'   => 'lib/validations.js',
                'template'      => 'cli/lib/validations.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/spinner.js',
                'template'      => 'cli/lib/spinner.js.twig',
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
                'destination'   => 'lib/paginate.js',
                'template'      => 'cli/lib/paginate.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => 'cli/lib/client.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/id.js',
                'template'      => 'cli/lib/id.js.twig',
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
                'destination'   => 'lib/commands/pull.js',
                'template'      => 'cli/lib/commands/pull.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/push.js',
                'template'      => 'cli/lib/commands/push.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/run.js',
                'template'      => 'cli/lib/commands/run.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/emulation/docker.js',
                'template'      => 'cli/lib/emulation/docker.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/emulation/utils.js',
                'template'      => 'cli/lib/emulation/utils.js.twig',
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
     * @param array $parameter
     * @param array $nestedTypes
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
        return match ($parameter['type']) {
            self::TYPE_INTEGER,
            self::TYPE_NUMBER => 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_FILE => 'string',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_OBJECT => 'object',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? $this->getTypeName($parameter['array']) . '[]'
                : 'string[]',
            default => $parameter['type'],
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

    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('caseKebab', function ($value) {
                return strtolower(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/', '-$1', $value));
            })
        ]);
    }
    /**
     * Language specific filters.
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            /** Return true if the entered service->method is enabled for a console preview link */
            new TwigFunction('hasConsolePreview', fn($method, $service) => preg_match('/^([Gg]et|[Ll]ist)/', $method)
                && !in_array(strtolower($method), $this->consoleIgnoreFunctions)
                && !in_array($service, $this->consoleIgnoreServices)),
        ];
    }
}
