<?php

namespace Appwrite\SDK\Language\Concern;

/**
 * Spec analysis shared by every generated Appwrite CLI, whatever the target
 * language.
 *
 * These helpers decide the command surface: which flags a method gets, which
 * services are scoped by a header, which methods are promoted to root
 * commands, and which console fallbacks a service needs. They are pure
 * functions of the API spec, so the TypeScript and Go CLIs have to agree on
 * every one of them -- docs/go-cli/PLAN.md lists the flag surface as
 * invariant 1.
 *
 * Sharing them here instead of reimplementing per language is the mechanism
 * that stops the two CLIs drifting apart.
 *
 * Language-specific concerns stay in the Language class: how a flag is
 * declared, how a variable is named, and how an argument reaches the SDK call.
 */
trait CliCommandSurface
{
    /**
     * Keyword list used to disambiguate generated flag names.
     *
     * Frozen deliberately. `getCliOptionName()` prefixes a colliding flag with
     * `x`, so this list is part of the CLI's public flag surface rather than a
     * property of the target language. Deriving it from the language's own
     * keywords would make the Go CLI emit different flags from the TypeScript
     * one, breaking invariant 1 in docs/go-cli/PLAN.md.
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
     * Convert string to kebab-case.
     */
    protected function toKebabCase(string $value): string
    {
        $value = preg_replace('/([a-z])([A-Z])/', '$1-$2', $value);
        $value = preg_replace('/[\s_]+/', '-', (string) $value);
        return strtolower((string) $value);
    }

    protected function hasArrayQueriesParameter(array $method): bool
    {
        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            if (($parameter['name'] ?? '') === 'queries' && ($parameter['type'] ?? '') === self::TYPE_ARRAY) {
                return true;
            }
        }

        return false;
    }

    protected function hasOnlyLimitOffsetQueries(array $method): bool
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
     * How a header-scoped service is spelled in the generated CLI.
     *
     * `service.resourceHeader` says which security scheme names the service's
     * own resource; this turns that into the label, variable, flag and factory
     * the template emits. Returns null for the usual case where the target is a
     * path parameter.
     *
     * @return array{label: string, idVar: string, flag: string, factory: string}|null
     */
    protected function getCliServiceScope(array $service): ?array
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
    protected function getCliTopLevelAliases(array $service): array
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
    protected function getCliCommandTargets(array $method, array $service): array
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
    protected function getCliFallbackHelpers(array $service): array
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

    protected function hasCliQueryParam(array $service): bool
    {
        foreach ($service['methods'] ?? [] as $method) {
            if ($this->hasArrayQueriesParameter($method)) {
                return true;
            }
        }

        return false;
    }

    protected function getCliOptionName(string $name): string
    {
        $kebabName = strtolower((string) preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s_]|(?<=[A-Z])\d)/', '-$1', $name));
        $kebabName = trim((string) preg_replace('/-+/', '-', str_replace('_', '-', $kebabName)), '-');

        return in_array(strtolower($name), self::FLAG_RESERVED_KEYWORDS, true) ? 'x' . $kebabName : $kebabName;
    }

    protected function getCliQueryConfig(array $method): array
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
}
