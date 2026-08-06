<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language\Concern\CliCommandSurface;
use Override;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CLI extends Node
{
    use CliCommandSurface;

    /**
     * List of functions to ignore for console preview.
     */
    private array $consoleIgnoreFunctions = [
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
     */
    private array $consoleIgnoreServices = [
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
    #[Override]
    protected $params = [
        'npmPackage' => 'packageName',
        'executableName' => 'executable',
        'logo' => '',
        'logoUnescaped' => '',
        'homebrewTapOwner' => 'appwrite',
        'homebrewTapName' => 'appwrite',
        'homebrewTapBranch' => 'main',
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

    #[Override]
    public function getName(): string
    {
        return 'cli';
    }

    /**
     * @return $this
     */
    public function setExecutableName(string $name): self
    {
        $this->setParam('executableName', $name);

        return $this;
    }

    /**
     * @return $this
     */
    public function setLogo(string $logo): self
    {
        $this->setParam('logo', $logo);

        return $this;
    }

    /**
     * @return $this
     */
    public function setLogoUnescaped(string $logo): self
    {
        $this->setParam('logoUnescaped', $logo);

        return $this;
    }

    /**
     * Configure the Homebrew tap (`<owner>/homebrew-<name>`) hosting the CLI formula.
     *
     * @param string $owner  Tap owner (e.g. "appwrite")
     * @param string $name   Tap short name without the `homebrew-` prefix
     * @param string $branch Default branch of the tap repository
     * @return $this
     */
    public function setHomebrewTap(string $owner, string $name, string $branch = 'main'): self
    {
        $this->setParam('homebrewTapOwner', $owner);
        $this->setParam('homebrewTapName', $name);
        $this->setParam('homebrewTapBranch', $branch);

        return $this;
    }

    /**
     * Escape reserved keywords.
     */
    #[Override]
    public function escapeKeyword(string $name): string
    {
        $reserved = $this->reservedKeywords;
        if (in_array(strtolower($name), $reserved)) {
            return 'x' . ucfirst($name);
        }
        return $name;
    }

    #[Override]
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
                'destination'   => '.npmrc',
                'template'      => 'cli/.npmrc',
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
                'destination'   => 'bun.lock',
                'template'      => 'cli/bun.lock.twig',
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
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'cli/package-lock.json.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'tsconfig.json',
                'template'      => 'cli/tsconfig.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'eslint.config.js',
                'template'      => 'cli/eslint.config.js',
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
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/ci.yml',
                'template'      => 'cli/.github/workflows/ci.yml',
            ],

            // Documentation
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'cli/docs/example.md.twig',
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
                'destination'   => 'lib/errors.ts',
                'template'      => 'cli/lib/errors.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/console-fallback.ts',
                'template'      => 'cli/lib/console-fallback.ts',
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
                'scope'         => 'copy',
                'destination'   => 'lib/context.ts',
                'template'      => 'cli/lib/context.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/completions.ts',
                'template'      => 'cli/lib/completions.ts',
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
                'destination'   => 'lib/response-config.ts',
                'template'      => 'cli/lib/response-config.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/hints.ts',
                'template'      => 'cli/lib/hints.ts',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/help.ts',
                'template'      => 'cli/lib/help.ts.twig',
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
                'destination'   => 'lib/flags.ts',
                'template'      => 'cli/lib/flags.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/auth/oauth.ts',
                'template'      => 'cli/lib/auth/oauth.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/auth/refresh-token.ts',
                'template'      => 'cli/lib/auth/refresh-token.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/auth/session.ts',
                'template'      => 'cli/lib/auth/session.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/auth/capabilities.ts',
                'template'      => 'cli/lib/auth/capabilities.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'lib/auth/login.ts',
                'template'      => 'cli/lib/auth/login.ts',
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
                'destination'   => 'lib/commands/generators/typescript/templates/constants.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/constants.ts.hbs',
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
                'destination'   => '/lib/commands/services/{{service.name | caseLower}}.ts',
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
            [
                'scope'         => 'copy',
                'destination'   => 'lib/commands/utils/query.ts',
                'template'      => 'cli/lib/commands/utils/query.ts',
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
     * @param array $nestedTypes
     */
    #[Override]
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst((string) $parameter['name']);
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
     * Language specific filters.
     */
    #[Override]
    public function getFilters(): array
    {
        return array_merge(parent::getFilters(), [
            new TwigFilter('hasCliQueryParam', fn (array $service): bool => $this->hasCliQueryParam($service)),
            new TwigFilter('cliServiceScope', fn (array $service): ?array => $this->getCliServiceScope($service)),
            new TwigFilter('cliQueryConfig', fn (array $method): array => $this->getCliQueryConfig($method)),
            new TwigFilter('cliTopLevelAliases', fn (array $service): array => $this->getCliTopLevelAliases($service)),
            new TwigFilter('cliCommandTargets', fn (array $method, array $service): array => $this->getCliCommandTargets($method, $service)),
            new TwigFilter('cliFallbackHelpers', fn (array $service): array => $this->getCliFallbackHelpers($service)),
            new TwigFilter('cliIsTopLevelAlias', fn (array $method, array $service): bool => $this->isCliTopLevelAlias($method, $service)),
        ]);
    }

    /**
     * Language specific functions.
     */
    #[Override]
    public function getFunctions(): array
    {
        return array_merge($this->getCliHelpFunctions(), [
            /** Return true if the entered service->method is enabled for a console preview link */
            new TwigFunction('hasConsolePreview', fn($method, $service): bool => preg_match('/^([Gg]et|[Ll]ist)/', (string) $method)
                && !in_array(strtolower((string) $method), $this->consoleIgnoreFunctions)
                && !in_array($service, $this->consoleIgnoreServices)),

            /**
             * Get CLI option definition for a parameter.
             * Returns array with: method, syntax, parser, customParserCode
             */
            new TwigFunction('getCliOption', function (array $parameter): array {
                $optionName = $this->getCliOptionName($parameter['name']);
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
                } elseif ($type === 'boolean') {
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
            }),

            /**
             * Get the variable name that commander.js will provide for a parameter.
             * This matches the option name converted to camelCase.
             */
            new TwigFunction('getCliVarName', function (array $parameter): string {
                $optionName = $this->getCliOptionName($parameter['name']);
                return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName))));
            }),

            /**
             * Get CLI argument expression for a parameter when calling the SDK method.
             * Handles JSON parsing for objects, or plain variable.
             */
            new TwigFunction('getCliArgExpression', function (array $parameter): string {
                $optionName = $this->getCliOptionName($parameter['name']);
                $varName = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName))));
                $type = $parameter['type'] ?? 'string';

                if ($type === 'object') {
                    return "parseJsonObject({$varName}, \"--{$optionName}\")";
                } elseif ($type === 'file') {
                    return "{$varName} !== undefined ? await resolveFileParam({$varName}) : undefined";
                } else {
                    return $varName;
                }
            }),
        ]);
    }
}
