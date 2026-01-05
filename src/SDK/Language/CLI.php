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
                'destination'   => 'CHANGELOG.md',
                'template'      => 'cli/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'cli/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => 'cli/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scoop/appwrite.config.json',
                'template'      => 'cli/scoop/appwrite.config.json.twig',
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
                'destination'   => 'index.ts',
                'template'      => 'cli/index.ts.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
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
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'cli/.github/workflows/publish.yml',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/sdks.ts',
                'template'      => 'cli/lib/sdks.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/attribute.ts',
                'template'      => 'cli/lib/type-generation/attribute.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/language.ts',
                'template'      => 'cli/lib/type-generation/languages/language.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/php.ts',
                'template'      => 'cli/lib/type-generation/languages/php.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/typescript.ts',
                'template'      => 'cli/lib/type-generation/languages/typescript.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/javascript.ts',
                'template'      => 'cli/lib/type-generation/languages/javascript.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/kotlin.ts',
                'template'      => 'cli/lib/type-generation/languages/kotlin.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/swift.ts',
                'template'      => 'cli/lib/type-generation/languages/swift.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/java.ts',
                'template'      => 'cli/lib/type-generation/languages/java.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/dart.ts',
                'template'      => 'cli/lib/type-generation/languages/dart.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/type-generation/languages/csharp.ts',
                'template'      => 'cli/lib/type-generation/languages/csharp.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/questions.ts',
                'template'      => 'cli/lib/questions.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/validations.ts',
                'template'      => 'cli/lib/validations.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/spinner.ts',
                'template'      => 'cli/lib/spinner.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/parser.ts',
                'template'      => 'cli/lib/parser.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/exception.ts',
                'template'      => 'cli/lib/exception.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/config.ts',
                'template'      => 'cli/lib/config.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/paginate.ts',
                'template'      => 'cli/lib/paginate.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.ts',
                'template'      => 'cli/lib/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/id.ts',
                'template'      => 'cli/lib/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/utils.ts',
                'template'      => 'cli/lib/utils.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/types.ts',
                'template'      => 'cli/lib/types.ts.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'cli/lib/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/init.ts',
                'template'      => 'cli/lib/commands/init.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/pull.ts',
                'template'      => 'cli/lib/commands/pull.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/push.ts',
                'template'      => 'cli/lib/commands/push.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/run.ts',
                'template'      => 'cli/lib/commands/run.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/emulation/docker.ts',
                'template'      => 'cli/lib/emulation/docker.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/emulation/utils.ts',
                'template'      => 'cli/lib/emulation/utils.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/commands/{{service.name | caseKebab}}.ts',
                'template'      => 'cli/lib/commands/command.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/generic.ts',
                'template'      => 'cli/lib/commands/generic.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/organizations.ts',
                'template'      => 'cli/lib/commands/organizations.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/types.ts',
                'template'      => 'cli/lib/commands/types.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/commands/update.ts',
                'template'      => 'cli/lib/commands/update.ts.twig',
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
        if (!empty($parameter['array']['model'])) {
            return $this->toPascalCase($parameter['array']['model']) . '[]';
        }
        if (!empty($parameter['model'])) {
            $modelType = $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? $modelType . '[]' : $modelType;
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER,
            self::TYPE_NUMBER => 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_FILE => 'string',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_OBJECT => 'string',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? $this->getTypeName($parameter['array']) . '[]'
                : 'any[]',
            default => $parameter['type'],
        };
    }

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
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
