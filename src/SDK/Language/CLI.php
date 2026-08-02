<?php

namespace Appwrite\SDK\Language;

use Override;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CLI extends Node
{
    private const array QUERY_FLAG_PARAMS = [
        'filtering' => ['filter', 'where', 'sortAsc', 'sortDesc', 'cursorAfter', 'cursorBefore'],
        'pagination' => ['limit', 'offset'],
        'select' => ['select'],
    ];

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
     * Convert string to kebab-case.
     */
    protected function toKebabCase(string $value): string
    {
        $value = preg_replace('/([a-z])([A-Z])/', '$1-$2', $value);
        $value = preg_replace('/[\s_]+/', '-', (string) $value);
        return strtolower((string) $value);
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

    private function hasArrayQueriesParameter(array $method): bool
    {
        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            if (($parameter['name'] ?? '') === 'queries' && ($parameter['type'] ?? '') === self::TYPE_ARRAY) {
                return true;
            }
        }

        return false;
    }

    private function hasOnlyLimitOffsetQueries(array $method): bool
    {
        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            if (($parameter['name'] ?? '') !== 'queries' || ($parameter['type'] ?? '') !== self::TYPE_ARRAY) {
                continue;
            }

            if (str_contains(strtolower($parameter['description'] ?? ''), 'only supported methods are limit and offset')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Client factory per scheme that names a service's own resource.
     * Organization scopes the console client, Project is the project client, so
     * the names cannot be derived from the scheme.
     */
    private const array SCOPE_FACTORIES = [
        'Organization' => 'sdkForConsoleWithOrganization',
        'Project' => 'sdkForProject',
    ];

    /**
     * Methods that are also registered as top-level root commands.
     *
     * The service subcommand stays (hidden from help) for backwards
     * compatibility; the root command is what `--help` advertises.
     *
     * TODO: Move CLI alias configuration to the API spec.
     *
     * @var array<string, list<string>>
     */
    private const array TOP_LEVEL_COMMANDS = [
        'oauth2' => ['listOrganizations', 'listProjects'],
    ];

    /**
     * Methods whose endpoint only exists on Cloud, mapped to the
     * `lib/console-fallback.ts` helper the command calls instead of the service
     * method. The helper keeps the Cloud call and falls back to the console
     * endpoints that self-hosted installs do serve.
     *
     * @var array<string, array<string, string>>
     */
    private const array CONSOLE_FALLBACK_METHODS = [
        'oauth2' => [
            'listOrganizations' => 'listOrganizationsForSession',
            'listProjects'      => 'listProjectsForSession',
        ],
        'organization' => [
            'get' => 'getOrganizationForSession',
        ],
    ];

    /**
     * How a header-scoped service is spelled in the generated CLI.
     *
     * `service.resourceHeader` says which security scheme names the service's
     * own resource; this turns that into the label, variable, flag and factory
     * the template emits. Returns null for the usual case where the target is a
     * path parameter.
     *
     * @return array{label: string, idVar: string, flag: string, factory: string}|null
     */
    private function getCliServiceScope(array $service): ?array
    {
        // Defaults to '' rather than null: most services are unscoped, and null
        // is not a valid array offset.
        $scheme = $service['resourceHeader'] ?? '';
        $factory = self::SCOPE_FACTORIES[$scheme] ?? null;

        if ($factory === null) {
            return null;
        }

        $idVar = \lcfirst($scheme) . 'Id';

        return [
            'label' => $scheme,
            'idVar' => $idVar,
            'flag' => $this->getCliOptionName($idVar),
            'factory' => $factory,
        ];
    }

    /**
     * Top-level root aliases for a service, used by `cli.ts` to import and
     * register the promoted commands.
     *
     * Only methods that survive exclusion filtering (and are therefore emitted
     * by the service template) are returned, so `cli.ts` never imports a
     * nonexistent export.
     *
     * @return list<array{method: string, command: string, export: string}>
     */
    private function getCliTopLevelAliases(array $service): array
    {
        $serviceName = $service['name'] ?? '';
        $configured = self::TOP_LEVEL_COMMANDS[$serviceName] ?? [];

        if ($configured === []) {
            return [];
        }

        $available = [];
        foreach ($service['methods'] ?? [] as $method) {
            $name = $method['name'] ?? '';

            if ($name !== '') {
                $available[$name] = true;
            }
        }

        $aliases = [];

        foreach ($configured as $methodName) {
            if (!isset($available[$methodName])) {
                continue;
            }

            $aliases[] = [
                'method' => $methodName,
                'command' => $this->toKebabCase($methodName),
                'export' => lcfirst($serviceName) . ucfirst($methodName) . 'RootCommand',
            ];
        }

        return $aliases;
    }

    /**
     * Emission targets for one method: the normal (possibly hidden) service
     * subcommand, plus a standalone root command when the method is aliased.
     *
     * @return list<array{var: string, standalone: bool, hidden: bool, implementation: string|null}>
     */
    private function getCliCommandTargets(array $method, array $service): array
    {
        $serviceName = $service['name'] ?? '';
        $methodName = $method['name'] ?? '';
        $commandVar = lcfirst($serviceName) . ucfirst($methodName) . 'Command';
        $isTopLevel = in_array($methodName, self::TOP_LEVEL_COMMANDS[$serviceName] ?? [], true);
        $implementation = self::CONSOLE_FALLBACK_METHODS[$serviceName][$methodName] ?? null;

        $targets = [
            [
                'var' => $commandVar,
                'standalone' => false,
                'hidden' => $isTopLevel,
                'implementation' => $implementation,
            ],
        ];

        if ($isTopLevel) {
            $targets[] = [
                'var' => lcfirst($serviceName) . ucfirst($methodName) . 'RootCommand',
                'standalone' => true,
                'hidden' => false,
                'implementation' => $implementation,
            ];
        }

        return $targets;
    }

    /**
     * Console fallback helpers this service's emitted commands call, so the
     * template imports exactly what it uses.
     *
     * @return list<string>
     */
    private function getCliFallbackHelpers(array $service): array
    {
        $configured = self::CONSOLE_FALLBACK_METHODS[$service['name'] ?? ''] ?? [];
        $helpers = [];

        foreach ($service['methods'] ?? [] as $method) {
            $helper = $configured[$method['name'] ?? ''] ?? null;

            if ($helper !== null && !in_array($helper, $helpers, true)) {
                $helpers[] = $helper;
            }
        }

        sort($helpers);

        return $helpers;
    }

    private function hasCliQueryParam(array $service): bool
    {
        foreach ($service['methods'] ?? [] as $method) {
            if ($this->hasArrayQueriesParameter($method)) {
                return true;
            }
        }

        return false;
    }

    private function getCliOptionName(string $name): string
    {
        $kebabName = strtolower((string) preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s_]|(?<=[A-Z])\d)/', '-$1', $name));
        $kebabName = trim((string) preg_replace('/-+/', '-', str_replace('_', '-', $kebabName)), '-');

        return in_array(strtolower($name), $this->reservedKeywords) ? 'x' . $kebabName : $kebabName;
    }

    private function getCliQueryConfig(array $method): array
    {
        $hasQueries = $this->hasArrayQueriesParameter($method);
        $methodName = $method['name'] ?? '';
        $parameterNames = array_map(
            fn (array $parameter): string => $parameter['name'] ?? '',
            $method['parameters']['all'] ?? []
        );
        $collides = fn (string $group): bool => array_intersect(self::QUERY_FLAG_PARAMS[$group], $parameterNames) !== [];
        $hasOnlyLimitOffsetQueries = $hasQueries && $this->hasOnlyLimitOffsetQueries($method);
        $hasSelectQueries = $hasQueries && in_array($methodName, ['listDocuments', 'getDocument', 'listRows', 'getRow'], true) && !$collides('select');
        $hasSelectionOnlyQueries = $hasQueries && in_array($methodName, ['getDocument', 'getRow'], true);
        $hasFilteringQueries = $hasQueries && !$hasOnlyLimitOffsetQueries && !$hasSelectionOnlyQueries && !$collides('filtering');
        $hasPaginationQueries = $hasQueries && !$hasSelectionOnlyQueries && !$collides('pagination');

        $builderParams = [];
        if ($hasQueries) {
            $builderParams[] = 'queries';

            if ($hasFilteringQueries) {
                $builderParams = array_merge($builderParams, self::QUERY_FLAG_PARAMS['filtering']);
            }

            if ($hasPaginationQueries) {
                $builderParams = array_merge($builderParams, self::QUERY_FLAG_PARAMS['pagination']);
            }

            if ($hasSelectQueries) {
                $builderParams = array_merge($builderParams, self::QUERY_FLAG_PARAMS['select']);
            }
        }

        if ($hasOnlyLimitOffsetQueries) {
            $rawDescriptionPrefix = 'Raw Appwrite JSON query strings (legacy). Use this for advanced queries or automation; for common pagination prefer --limit and --offset. When mixed, raw --queries are sent before generated flag queries.';
        } elseif ($hasSelectionOnlyQueries) {
            $rawDescriptionPrefix = 'Raw Appwrite JSON query strings (legacy). Use this for advanced queries or automation; for selecting returned attributes prefer --select. When mixed, raw --queries are sent before generated flag queries.';
        } elseif ($hasSelectQueries) {
            $rawDescriptionPrefix = 'Raw Appwrite JSON query strings (legacy). Use this for advanced queries or automation; for common filtering, sorting, pagination, and selection prefer --filter, --sort-asc, --sort-desc, --limit, --offset, and --select. When mixed, raw --queries are sent before generated flag queries.';
        } else {
            $rawDescriptionPrefix = 'Raw Appwrite JSON query strings (legacy). Use this for advanced queries or automation; for common filtering, sorting, and pagination prefer --filter, --sort-asc, --sort-desc, --limit, and --offset. When mixed, raw --queries are sent before generated flag queries.';
        }

        return [
            'hasQueries' => $hasQueries,
            'hasFiltering' => $hasFilteringQueries,
            'hasPagination' => $hasPaginationQueries,
            'hasCursors' => $hasFilteringQueries,
            'hasSelect' => $hasSelectQueries,
            'builderParams' => $builderParams,
            'extraParams' => array_values(array_filter($builderParams, fn (string $param): bool => $param !== 'queries')),
            'rawDescriptionPrefix' => $rawDescriptionPrefix,
        ];
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
                'scope'         => 'copy',
                'destination'   => 'lib/help.ts',
                'template'      => 'cli/lib/help.ts',
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

    #[Override]
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
                    if (str_contains((string) $example, '[') && str_contains((string) $example, ']')) {
                        $trimmed = substr((string) $example, 1, -1);
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
            new TwigFilter('cliIsTopLevelAlias', fn (array $method, array $service): bool => in_array(
                $method['name'] ?? '',
                self::TOP_LEVEL_COMMANDS[$service['name'] ?? ''] ?? [],
                true,
            )),
        ]);
    }

    /**
     * Language specific functions.
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
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
        ];
    }
}
