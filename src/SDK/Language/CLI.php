<?php

namespace Appwrite\SDK\Language;

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
     * List of reserved keywords.
     * @var array
     */
    protected $reservedKeywords = [
        'default',
        'class',
        'function',
        'switch',
        'case',
        'break',
        'continue',
        'return',
        'if',
        'else',
        'for',
        'while',
        'do',
        'try',
        'catch',
        'throw',
        'new',
        'delete',
        'typeof',
        'void',
        'this',
        'in',
        'instanceof',
        'var',
        'let',
        'const',
        'true',
        'false',
        'null',
        'private'
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'cli';
    }

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
     * Convert string to kebab-case.
     * @param string $value
     * @return string
     */
    protected function toKebabCase(string $value): string
    {
        $value = preg_replace('/([a-z])([A-Z])/', '$1-$2', $value);
        $value = preg_replace('/[\s_]+/', '-', $value);
        return strtolower($value);
    }

    /**
     * Escape reserved keywords.
     * @param string $name
     * @return string
     */
    public function escapeKeyword(string $name): string
    {
        $reserved = $this->reservedKeywords;
        if (in_array(strtolower($name), $reserved)) {
            return 'x' . ucfirst($name);
        }
        return $name;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            // Root configuration files
            [
                'scope'         => 'default',
                'destination'   => '.gitignore',
                'template'      => 'cli/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'cli/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'bun-types.d.ts',
                'template'      => 'cli/bun-types.d.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'bunfig.toml',
                'template'      => 'cli/bunfig.toml',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => 'cli/LICENSE.md.twig',
            ],
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
                'scope'         => 'copy',
                'destination'   => 'tsconfig.json',
                'template'      => 'cli/tsconfig.json',
            ],

            // Entry points
            [
                'scope'         => 'default',
                'destination'   => 'cli.ts',
                'template'      => 'cli/cli.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'index.ts',
                'template'      => 'cli/index.ts',
            ],

            // Installation scripts
            [
                'scope'         => 'default',
                'destination'   => 'install.ps1',
                'template'      => 'cli/install.ps1.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'install.sh',
                'template'      => 'cli/install.sh.twig',
            ],

            // GitHub workflows
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'cli/.github/workflows/publish.yml',
            ],

            // Documentation
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'cli/docs/example.md.twig',
            ],

            // Distribution - Formula (Homebrew)
            [
                'scope'         => 'method',
                'destination'   => 'Formula/{{ language.params.executableName }}.rb',
                'template'      => 'cli/Formula/formula.rb.twig',
            ],

            // Distribution - Scoop (Windows)
            [
                'scope'         => 'default',
                'destination'   => 'scoop/appwrite.config.json',
                'template'      => 'cli/scoop/appwrite.config.json.twig',
                'minify'        => false,
            ],

            // Core library files (lib/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/client.ts',
                'template'      => 'cli/lib/client.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/json.ts',
                'template'      => 'cli/lib/json.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/config.ts',
                'template'      => 'cli/lib/config.ts',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/constants.ts',
                'template'      => 'cli/lib/constants.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/id.ts',
                'template'      => 'cli/lib/id.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/paginate.ts',
                'template'      => 'cli/lib/paginate.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/parser.ts',
                'template'      => 'cli/lib/parser.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/questions.ts',
                'template'      => 'cli/lib/questions.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/sdks.ts',
                'template'      => 'cli/lib/sdks.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/services.ts',
                'template'      => 'cli/lib/services.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/spinner.ts',
                'template'      => 'cli/lib/spinner.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/types.ts',
                'template'      => 'cli/lib/types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/utils.ts',
                'template'      => 'cli/lib/utils.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/validations.ts',
                'template'      => 'cli/lib/validations.ts',
            ],

            // Shared utilities (lib/shared/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/shared/typescript-type-utils.ts',
                'template'      => 'cli/lib/shared/typescript-type-utils.ts',
            ],

            // Commands (lib/commands/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/config.ts',
                'template'      => 'cli/lib/commands/config.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/config-validations.ts',
                'template'      => 'cli/lib/commands/config-validations.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generate.ts',
                'template'      => 'cli/lib/commands/generate.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/base.ts',
                'template'      => 'cli/lib/commands/generators/base.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/index.ts',
                'template'      => 'cli/lib/commands/generators/index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/language-detector.ts',
                'template'      => 'cli/lib/commands/generators/language-detector.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/typescript/databases.ts',
                'template'      => 'cli/lib/commands/generators/typescript/databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/typescript/templates/types.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/types.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/typescript/templates/databases.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/databases.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generators/typescript/templates/index.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/index.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/errors.ts',
                'template'      => 'cli/lib/commands/errors.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/generic.ts',
                'template'      => 'cli/lib/commands/generic.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/init.ts',
                'template'      => 'cli/lib/commands/init.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/pull.ts',
                'template'      => 'cli/lib/commands/pull.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/push.ts',
                'template'      => 'cli/lib/commands/push.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/run.ts',
                'template'      => 'cli/lib/commands/run.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/schema.ts',
                'template'      => 'cli/lib/commands/schema.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/types.ts',
                'template'      => 'cli/lib/commands/types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/update.ts',
                'template'      => 'cli/lib/commands/update.ts',
            ],

            // Command services (lib/commands/services/)
            [
                'scope'         => 'service',
                'destination'   => '/lib/commands/services/{{service.name | caseKebab}}.ts',
                'template'      => 'cli/lib/commands/services/services.ts.twig',
            ],

            // Command utilities (lib/commands/utils/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/attributes.ts',
                'template'      => 'cli/lib/commands/utils/attributes.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/change-approval.ts',
                'template'      => 'cli/lib/commands/utils/change-approval.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/database-sync.ts',
                'template'      => 'cli/lib/commands/utils/database-sync.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/deployment.ts',
                'template'      => 'cli/lib/commands/utils/deployment.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/error-formatter.ts',
                'template'      => 'cli/lib/commands/utils/error-formatter.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/pools.ts',
                'template'      => 'cli/lib/commands/utils/pools.ts',
            ],

            // Emulation (lib/emulation/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/emulation/docker.ts',
                'template'      => 'cli/lib/emulation/docker.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/emulation/utils.ts',
                'template'      => 'cli/lib/emulation/utils.ts',
            ],

            // Type generation (lib/type-generation/)
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/attribute.ts',
                'template'      => 'cli/lib/type-generation/attribute.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/csharp.ts',
                'template'      => 'cli/lib/type-generation/languages/csharp.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/dart.ts',
                'template'      => 'cli/lib/type-generation/languages/dart.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/java.ts',
                'template'      => 'cli/lib/type-generation/languages/java.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/javascript.ts',
                'template'      => 'cli/lib/type-generation/languages/javascript.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/kotlin.ts',
                'template'      => 'cli/lib/type-generation/languages/kotlin.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/language.ts',
                'template'      => 'cli/lib/type-generation/languages/language.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/php.ts',
                'template'      => 'cli/lib/type-generation/languages/php.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/swift.ts',
                'template'      => 'cli/lib/type-generation/languages/swift.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/type-generation/languages/typescript.ts',
                'template'      => 'cli/lib/type-generation/languages/typescript.ts',
            ],
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

            /**
             * Get CLI option definition for a parameter.
             * Returns array with: method, syntax, parser, customParserCode
             */
            new TwigFunction('getCliOption', function (array $parameter): array {
                $name = $parameter['name'];
                $kebabName = strtolower(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s]|(?<=[A-Z])[0-9_])/', '-$1', $name));
                $optionName = in_array(strtolower($name), $this->reservedKeywords) ? 'x' . $kebabName : $kebabName;
                $type = $parameter['type'] ?? 'string';
                $required = $parameter['required'] ?? false;

                if ($required) {
                    if ($type === 'boolean') {
                        return [
                            'method' => 'requiredOption',
                            'syntax' => "--{$optionName} <{$optionName}>",
                            'parser' => 'parseBool',
                        ];
                    } elseif ($type === 'integer' || $type === 'number') {
                        return [
                            'method' => 'requiredOption',
                            'syntax' => "--{$optionName} <{$optionName}>",
                            'parser' => 'parseInteger',
                        ];
                    } elseif ($type === 'array') {
                        return [
                            'method' => 'requiredOption',
                            'syntax' => "--{$optionName} [{$optionName}...]",
                            'parser' => null,
                        ];
                    } else {
                        return [
                            'method' => 'requiredOption',
                            'syntax' => "--{$optionName} <{$optionName}>",
                            'parser' => null,
                        ];
                    }
                } else {
                    if ($type === 'boolean') {
                        return [
                            'method' => 'option',
                            'syntax' => "--{$optionName} [value]",
                            'parser' => null,
                            'customParserCode' => "(value: string | undefined) =>\n      value === undefined ? true : parseBool(value)",
                        ];
                    } elseif ($type === 'integer' || $type === 'number') {
                        return [
                            'method' => 'option',
                            'syntax' => "--{$optionName} <{$optionName}>",
                            'parser' => 'parseInteger',
                        ];
                    } elseif ($type === 'array') {
                        return [
                            'method' => 'option',
                            'syntax' => "--{$optionName} [{$optionName}...]",
                            'parser' => null,
                        ];
                    } else {
                        return [
                            'method' => 'option',
                            'syntax' => "--{$optionName} <{$optionName}>",
                            'parser' => null,
                        ];
                    }
                }
            }),

            /**
             * Get the variable name that commander.js will provide for a parameter.
             * This matches the option name converted to camelCase.
             */
            new TwigFunction('getCliVarName', function (array $parameter): string {
                $name = $parameter['name'];
                $kebabName = strtolower(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s]|(?<=[A-Z])[0-9_])/', '-$1', $name));
                $optionName = in_array(strtolower($name), $this->reservedKeywords) ? 'x' . $kebabName : $kebabName;
                return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName))));
            }),

            /**
             * Get CLI argument expression for a parameter when calling the SDK method.
             * Handles enum casting, JSON parsing for objects, or plain variable.
             */
            new TwigFunction('getCliArgExpression', function (array $parameter): string {
                $name = $parameter['name'];
                $kebabName = strtolower(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s]|(?<=[A-Z])[0-9_])/', '-$1', $name));
                $optionName = in_array(strtolower($name), $this->reservedKeywords) ? 'x' . $kebabName : $kebabName;
                $varName = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName))));
                $type = $parameter['type'] ?? 'string';

                if (isset($parameter['enumName'])) {
                    $enumName = ucfirst($parameter['enumName']);
                    return "{$varName} as {$enumName}";
                } elseif ($type === 'object') {
                    return "JSON.parse({$varName})";
                } else {
                    return $varName;
                }
            }),
        ];
    }
}
