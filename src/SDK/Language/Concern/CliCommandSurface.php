<?php

namespace Appwrite\SDK\Language\Concern;

use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Model\Tag;
use Utopia\OpenAPI\Specification;
use Override;
use Twig\TwigFunction;

/**
 * Spec analysis shared by every generated Appwrite CLI: which flags a method
 * gets, which services a header scopes, which methods are promoted to root
 * commands, which console fallbacks a service needs. The flag surface is public
 * API, so sharing these is what stops the two CLIs drifting apart.
 *
 * How a flag is declared, a variable named, or an argument passed to the SDK
 * stays in the Language class.
 */
trait CliCommandSurface
{
    /**
     * Keyword list used to disambiguate generated flag names. Frozen: it is
     * part of the public flag surface, not a property of the target language,
     * so both CLIs prefix the same flags with `x`.
     *
     * @var list<string>
     */
    protected const array FLAG_RESERVED_KEYWORDS = [
        'default', 'class', 'function', 'switch', 'case', 'break', 'continue',
        'return', 'if', 'else', 'for', 'while', 'do', 'try', 'catch', 'throw',
        'new', 'delete', 'typeof', 'void', 'this', 'in', 'instanceof', 'var',
        'let', 'const', 'true', 'false', 'null', 'private',
    ];

    protected const array QUERY_FLAG_PARAMS = [
        'filtering' => ['filter', 'where', 'sortAsc', 'sortDesc', 'cursorAfter', 'cursorBefore'],
        'pagination' => ['limit', 'offset'],
        'select' => ['select'],
    ];

    /**
     * Client factory per scheme that names a service's own resource.
     * Organization scopes the console client, Project is the project client, so
     * the names cannot be derived from the scheme.
     */
    protected const array SCOPE_FACTORIES = [
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
    protected const array TOP_LEVEL_COMMANDS = [
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
    protected const array CONSOLE_FALLBACK_METHODS = [
        'oauth2' => [
            'listOrganizations' => 'listOrganizationsForSession',
            'listProjects'      => 'listProjectsForSession',
        ],
        'organization' => [
            'get' => 'getOrganizationForSession',
        ],
    ];

    /**
     * The main help screen, grouped by intent. Entries are command paths as
     * typed, so a root alias sits beside a command. A name the spec does not
     * produce is skipped; a command the spec produces that is named nowhere
     * still appears under OTHER.
     *
     * @var list<array{title: string, dim?: bool, commands: list<string>}>
     */
    protected const array HELP_GROUPS = [
        [
            'title' => 'GET STARTED',
            'commands' => [
                'login', 'list-organizations', 'list-projects', 'init', 'pull',
                'push', 'run', 'whoami',
            ],
        ],
        [
            'title' => 'PROJECT',
            'commands' => [
                'organization', 'project', 'apps', 'proxy', 'vcs', 'webhooks',
            ],
        ],
        [
            'title' => 'RESOURCES',
            'commands' => [
                'account', 'users', 'teams', 'tablesdb', 'storage', 'functions',
                'sites', 'messaging', 'tokens', 'backups', 'presences',
            ],
        ],
        [
            'title' => 'UTILITIES',
            'commands' => [
                'graphql', 'generate', 'types', 'locale', 'activities',
                'migrations', 'notifications', 'oauth2', 'client', 'completion',
                'logout', 'update',
            ],
        ],
        [
            'title' => 'DEPRECATED',
            'dim' => true,
            'commands' => ['databases'],
        ],
    ];

    /**
     * One-line summaries for the main help listing; a command's own description
     * stays the long form. Keep them under 51 characters to fit one terminal
     * line, imperative, no trailing period. `%title%` is the SDK title.
     *
     * @var array<string, string>
     */
    protected const array HELP_SUMMARIES = [
        'login' => 'Authenticate with your %title% account',
        'list-organizations' => 'Organizations your session can access',
        'list-projects' => 'Projects your session can access',
        'init' => 'Scaffold a project, function, site, or resource',
        'pull' => 'Pull remote project resources into this directory',
        'push' => 'Push local project resources',
        'run' => 'Run the project locally for development',
        'whoami' => 'Show the currently authenticated account',

        'organization' => 'Manage organization-level projects',
        'project' => 'Usage, variables, and project-level settings',
        'apps' => 'OAuth2 applications, keys, scopes, installations',
        'proxy' => 'Domain configuration beyond DNS',
        'vcs' => 'Connect and manage VCS repositories',
        'webhooks' => 'Project webhooks',

        'account' => 'Manage your own user account',
        'users' => 'Manage project users',
        'teams' => 'Group users to share resource access',
        'tablesdb' => 'Structured tables of rows and columns',
        'storage' => 'Files and buckets',
        'functions' => 'Serverless functions, deployments, and executions',
        'sites' => 'Static and SSR sites and their deployments',
        'messaging' => 'Topics, subscribers, and message delivery',
        'tokens' => 'Resource tokens for secure file access',
        'backups' => 'Backup policies, archives, and restorations',
        'presences' => 'Real-time user presence tracking',

        'graphql' => 'Query and mutate any resource via GraphQL',
        'generate' => 'Generate a type-safe SDK from your project config',
        'types' => 'Generate TypeScript types for your project',
        'locale' => 'Localize your app based on user location',
        'activities' => 'List and inspect project activity events',
        'migrations' => 'Migrate data between services',
        'notifications' => 'Console notifications',
        'oauth2' => 'Authorize apps and issue OAuth2 and OIDC tokens',
        'client' => 'Configure the CLI itself',
        'completion' => 'Generate shell completion scripts',
        'logout' => 'Log out of your %title% account',
        'update' => 'Update the CLI to the latest version',

        'databases' => 'Use `tablesdb` instead',
    ];

    /**
     * Order of the global flags on the main help screen, by long flag.
     * Unlisted options are appended.
     *
     * @var list<string>
     */
    protected const array HELP_OPTION_ORDER = [
        '--version', '--help', '--json', '--raw', '--show-secrets', '--verbose',
        '--force', '--all', '--id', '--report',
    ];

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
     * The method's array `queries` parameter, or null when it has none. Its
     * description is what says whether the endpoint accepts only limit and
     * offset, so the parameter is returned rather than just its presence.
     */
    protected function findQueriesParameter(Operation $method): ?Parameter
    {
        foreach ($this->getOperationParameters($method) as $parameter) {
            if ($parameter->name === 'queries' && $this->getSchemaType($parameter) === self::TYPE_ARRAY) {
                return $parameter;
            }
        }

        return null;
    }

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
    protected function getCliServiceScope(?string $resourceHeader): ?array
    {
        $scheme = $resourceHeader ?? '';
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
    protected function getCliTopLevelAliases(Tag $service, array $methods): array
    {
        $serviceName = $service->name;
        $configured = self::TOP_LEVEL_COMMANDS[$serviceName] ?? [];

        if ($configured === []) {
            return [];
        }

        $available = [];
        foreach ($methods as $method) {
            $name = $this->cliMethodName($method);

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
     * Whether a method is promoted to the root, which decides how the docs
     * example spells its invocation. Both CLIs document the same surface, so
     * the lookup belongs here rather than in either language class.
     */
    protected function isCliTopLevelAlias(Operation $method, Tag $service): bool
    {
        return \in_array(
            $this->cliMethodName($method),
            self::TOP_LEVEL_COMMANDS[$service->name] ?? [],
            true,
        );
    }

    /**
     * Emission targets for one method: the normal (possibly hidden) service
     * subcommand, plus a standalone root command when the method is aliased.
     *
     * @return list<array{var: string, standalone: bool, hidden: bool, implementation: string|null}>
     */
    protected function getCliCommandTargets(Operation $method, Tag $service): array
    {
        $serviceName = $service->name;
        $methodName = $this->cliMethodName($method);
        $commandVar = lcfirst($serviceName) . ucfirst((string) $methodName) . 'Command';
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
    protected function getCliFallbackHelpers(Tag $service, array $methods): array
    {
        $configured = self::CONSOLE_FALLBACK_METHODS[$service->name] ?? [];
        $helpers = [];

        foreach ($methods as $method) {
            $helper = $configured[$this->cliMethodName($method)] ?? null;

            if ($helper !== null && !in_array($helper, $helpers, true)) {
                $helpers[] = $helper;
            }
        }

        sort($helpers);

        return $helpers;
    }

    protected function hasCliQueryParam(array $methods): bool
    {
        return array_any($methods, fn($method): bool => $this->findQueriesParameter($method) !== null);
    }

    /**
     * GraphQL SDK methods take a JSON request object, but a CLI flag named
     * `--query` should also accept the GraphQL document users naturally type.
     * Keep the identification shared so the TypeScript and Go CLIs cannot
     * apply different input or help contracts.
     */
    protected function isCliGraphQLInput(Parameter $parameter, Operation $method, Tag $service): bool
    {
        return $service->name === 'graphql'
            && ($method->extensions['x-appwrite']['type'] ?? '') === 'graphql'
            && $parameter->name === 'query';
    }

    protected function getCliMethodDescription(Operation $method, Tag $service): string
    {
        if ($service->name === 'graphql' && ($method->extensions['x-appwrite']['type'] ?? '') === 'graphql') {
            return match ($this->cliMethodName($method)) {
                'query' => 'Execute a GraphQL query.',
                'mutation' => 'Execute a GraphQL mutation.',
                default => $method->description,
            };
        }

        return $method->description;
    }

    protected function getCliParameterDescription(Parameter $parameter, Operation $method, Tag $service): string
    {
        if ($this->isCliGraphQLInput($parameter, $method, $service)) {
            return 'Raw GraphQL document, or a JSON request object or array for variables, operation names, or batching.';
        }

        return $parameter->description;
    }

    protected function cliMethodName(Operation $method): string
    {
        return (string) ($method->extensions['x-appwrite']['method'] ?? $method->id);
    }

    protected function getCliOptionName(string $name): string
    {
        $kebabName = strtolower((string) preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s_]|(?<=[A-Z])\d)/', '-$1', $name));
        $kebabName = trim((string) preg_replace('/-+/', '-', str_replace('_', '-', $kebabName)), '-');

        return in_array(strtolower($name), self::FLAG_RESERVED_KEYWORDS, true) ? 'x' . $kebabName : $kebabName;
    }

    protected function getCliQueryConfig(Operation $method): array
    {
        $queries = $this->findQueriesParameter($method);
        $hasQueries = $queries !== null;
        $methodName = $this->cliMethodName($method);
        $parameterNames = array_map(
            static fn(Parameter $parameter): string => $parameter->name,
            $this->getOperationParameters($method),
        );
        $collides = fn (string $group): bool => array_intersect(self::QUERY_FLAG_PARAMS[$group], $parameterNames) !== [];
        $hasOnlyLimitOffsetQueries = $hasQueries
            && str_contains(strtolower((string) $queries->description), 'only supported methods are limit and offset');
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

    /**
     * @return list<array{title: string, dim: bool, commands: list<string>}>
     */
    protected function getCliHelpGroups(): array
    {
        return array_map(
            static fn (array $group): array => [
                'title' => $group['title'],
                'dim' => $group['dim'] ?? false,
                'commands' => $group['commands'],
            ],
            self::HELP_GROUPS,
        );
    }

    /**
     * Summaries with `%title%` resolved, so a template emits a plain literal.
     *
     * @return array<string, string>
     */
    protected function getCliHelpSummaries(string $title): array
    {
        return array_map(
            static fn (string $summary): string => str_replace('%title%', $title, $summary),
            self::HELP_SUMMARIES,
        );
    }

    /**
     * @return list<string>
     */
    protected function getCliHelpOptionOrder(): array
    {
        return self::HELP_OPTION_ORDER;
    }

    /**
     * Help metadata for the templates of every CLI that renders the grouped
     * main help screen.
     *
     * @return list<TwigFunction>
     */
    protected function getCliHelpFunctions(): array
    {
        return [
            new TwigFunction('cliHelpGroups', fn (): array => $this->getCliHelpGroups()),
            new TwigFunction('cliHelpSummaries', fn (string $title): array => $this->getCliHelpSummaries($title)),
            new TwigFunction('cliHelpOptionOrder', fn (): array => $this->getCliHelpOptionOrder()),
            new TwigFunction('cliIsGraphQLInput', fn(Parameter $parameter, Operation $method, Tag $service): bool => $this->isCliGraphQLInput($parameter, $method, $service)),
            new TwigFunction('cliMethodDescription', fn(Operation $method, Tag $service): string => $this->getCliMethodDescription($method, $service)),
            new TwigFunction('cliParameterDescription', fn(Parameter $parameter, Operation $method, Tag $service): string => $this->getCliParameterDescription($parameter, $method, $service)),
        ];
    }

    /**
     * How a value is spelled on the command line, for the docs examples. This
     * is a shell invocation, not source in either CLI's implementation
     * language, so it belongs to the shared surface: the target language of the
     * generated CLI cannot change how a user types an array of roles.
     *
     * A trait method wins over an inherited one, which is what keeps `GoCLI`
     * off `Go::getParamExample` -- that renders Go literals, and
     * `--roles []string{}` is not a command anybody can run.
     */
    #[Override]
    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type = $this->getSchemaType($param);
        $example = $this->getSchemaExample($param);

        if (empty($example) && $example !== 0 && $example !== false) {
            return match ($type) {
                self::TYPE_NUMBER, self::TYPE_INTEGER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_STRING => "''",
                self::TYPE_ARRAY => 'one two three',
                self::TYPE_OBJECT => '\'{ "key": "value" }\'',
                self::TYPE_FILE => "'path/to/file.png'",
                default => '',
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => (\str_contains((string) $example, '[') && \str_contains((string) $example, ']'))
                ? \implode(' ', \explode(',', \substr((string) $example, 1, -1)))
                : (string) $example,
            self::TYPE_OBJECT => '\'{ "key": "value" }\'',
            self::TYPE_NUMBER, self::TYPE_INTEGER => (string) $example,
            self::TYPE_BOOLEAN => $example ? 'true' : 'false',
            self::TYPE_STRING => (string) $example,
            self::TYPE_FILE => "'path/to/file.png'",
            default => '',
        };
    }
}
