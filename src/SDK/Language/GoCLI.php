<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language\Concern\CliCommandSurface;
use Override;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Go rewrite of the Appwrite CLI.
 *
 * Extends Go rather than CLI: the type mapping, keywords and doc-comment
 * filter all carry over, while the command surface comes from
 * CliCommandSurface -- the same trait the TypeScript CLI uses, so the two
 * cannot drift on flags, scopes, aliases or fallbacks.
 */
class GoCLI extends Go
{
    use CliCommandSurface;

    /**
     * Module the CLI imports the generated SDK from. Carries the /vN suffix Go
     * requires from v2 on, so it moves in lockstep with the version pinned in
     * templates/go-cli/go.mod.twig.
     */
    public const string SDK_MODULE = 'github.com/appwrite/sdk-for-go/v6';

    /**
     * @var array
     */
    #[Override]
    protected $params = [
        'executableName' => 'appwrite',
        // Names the npm package AND every release asset. install.sh and
        // install.ps1 build their download URL from this same param, so the
        // release build has to derive asset names from it rather than from
        // executableName -- they happen to agree today, and a rename would
        // silently break every installer if they did not.
        'npmPackage' => 'appwrite-cli',
        'sdkImportPath' => self::SDK_MODULE,
        // ASCII art above the main help screen, held RAW rather than escaped:
        // the TypeScript CLI's `logo` param is JSON-encoded, and JSON escapes
        // `/` as `\/`, which is not a valid Go escape. Each template escapes it
        // for its own language instead.
        'logo' => '',
        // The same art indented for install.sh and install.ps1, which are shared
        // verbatim with the TypeScript CLI and interpolate this param directly.
        'logoUnescaped' => '',
        'homebrewTapOwner' => 'appwrite',
        'homebrewTapName' => 'appwrite',
        'homebrewTapBranch' => 'main',
    ];

    #[Override]
    public function getName(): string
    {
        return 'GoCLI';
    }

    /**
     * Name of the binary the user invokes.
     */
    public function setExecutableName(string $executableName): self
    {
        $this->setParam('executableName', $executableName);

        return $this;
    }

    /**
     * ASCII art printed above the main help screen, unescaped.
     */
    public function setLogo(string $logo): self
    {
        $this->setParam('logo', $logo);

        return $this;
    }

    /**
     * ASCII art printed by the installer scripts.
     */
    public function setLogoUnescaped(string $logo): self
    {
        $this->setParam('logoUnescaped', $logo);

        return $this;
    }

    /**
     * Name of the npm package, which also names every release asset.
     */
    public function setNPMPackage(string $name): self
    {
        $this->setParam('npmPackage', $name);

        return $this;
    }

    /**
     * Configure the Homebrew tap (`<owner>/homebrew-<name>`) hosting the formula.
     */
    public function setHomebrewTap(string $owner, string $name, string $branch = 'main'): self
    {
        $this->setParam('homebrewTapOwner', $owner);
        $this->setParam('homebrewTapName', $name);
        $this->setParam('homebrewTapBranch', $branch);

        return $this;
    }

    /**
     * Render a value as a quoted Go string literal.
     *
     * `json_encode` is not usable here: it escapes `/` as `\/`, which Go
     * rejects as an unknown escape sequence. Go's escape rules are also
     * narrower than JSON's, so the set is spelled out rather than borrowed.
     */
    protected function toGoString(?string $value): string
    {
        $escaped = strtr($value ?? '', [
            '\\' => '\\\\',
            '"' => '\\"',
            "\n" => '\\n',
            "\r" => '\\r',
            "\t" => '\\t',
        ]);

        // Remaining control characters have no Go escape and would be a syntax
        // error inside an interpreted string literal.
        $escaped = (string) preg_replace_callback(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
            static fn (array $match): string => sprintf('\\x%02x', ord($match[0])),
            $escaped
        );

        return '"' . $escaped . '"';
    }

    /**
     * Go package name for a service.
     *
     * Package names are lowercase and unpunctuated, so `tablesDB` becomes
     * `tablesdb`.
     */
    protected function getGoPackageName(string $service): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $service));
    }

    /**
     * Identifiers that cannot be used for a flag's backing variable.
     *
     * Go keywords are a syntax error. The predeclared identifiers are legal to
     * shadow, but generated action bodies call `len`, `make`, `new` and friends,
     * so a parameter named `len` would break them in a way the compiler reports
     * far from the cause.
     *
     * Distinct from CliCommandSurface::FLAG_RESERVED_KEYWORDS on purpose: that
     * list shapes the user-visible flag name and is shared with the TypeScript
     * CLI, this one only shapes a private Go identifier.
     *
     * @var list<string>
     */
    private const array GO_RESERVED_IDENTIFIERS = [
        'break', 'case', 'chan', 'const', 'continue', 'default', 'defer', 'else',
        'fallthrough', 'for', 'func', 'go', 'goto', 'if', 'import', 'interface',
        'map', 'package', 'range', 'return', 'select', 'struct', 'switch', 'type',
        'var',
        'any', 'append', 'bool', 'byte', 'cap', 'clear', 'close', 'complex',
        'copy', 'delete', 'error', 'false', 'float32', 'float64', 'int', 'int8',
        'int16', 'int32', 'int64', 'len', 'make', 'max', 'min', 'new', 'nil',
        'panic', 'print', 'println', 'real', 'recover', 'rune', 'string', 'true',
        'uint', 'uint8', 'uint16', 'uint32', 'uint64', 'uintptr',
    ];

    /**
     * Go identifier for a flag's backing variable.
     *
     * Derived from the flag name so the two stay obviously related, then made
     * safe for Go. Renaming here never changes the flag itself.
     */
    protected function getGoVarName(string $name): string
    {
        $optionName = $this->getCliOptionName($name);
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $optionName))));

        return in_array($camel, self::GO_RESERVED_IDENTIFIERS, true)
            ? $camel . 'Arg'
            : $camel;
    }

    /**
     * How one spec parameter is registered as a pflag flag.
     *
     * Mirrors the TypeScript `getCliOption()` decision table so both CLIs
     * expose the same flag for the same parameter. Only the mechanism differs:
     * commander takes a syntax string, pflag takes a typed registration call.
     *
     * @return array{flag: string, var: string, register: string, goType: string, required: bool, noOptDefault: string|null}
     */
    protected function getGoCliOption(array $parameter): array
    {
        $flag = $this->getCliOptionName($parameter['name']);
        $type = $parameter['type'] ?? self::TYPE_STRING;
        $required = (bool) ($parameter['required'] ?? false);

        [$register, $goType, $noOptDefault] = match ($type) {
            self::TYPE_BOOLEAN => ['Bool', 'bool', $required ? null : 'true'],
            self::TYPE_INTEGER => ['Int', 'int', null],
            self::TYPE_NUMBER => ['Float64', 'float64', null],
            self::TYPE_ARRAY => ['StringArray', '[]string', null],
            // Objects arrive as a JSON string and are decoded in the action;
            // files arrive as a path and are opened there.
            default => ['String', 'string', null],
        };

        return [
            'flag' => $flag,
            'var' => $this->getGoVarName($parameter['name']),
            'register' => $register,
            'goType' => $goType,
            'required' => $required,
            'noOptDefault' => $noOptDefault,
        ];
    }

    /**
     * How one method is called on the generated Go SDK.
     *
     * The SDK takes required parameters positionally in spec order and optional
     * ones as functional options that are methods on the service, named
     * `With<Method><Param>`.
     *
     * A flag and the SDK parameter it feeds are not always the same Go type: a
     * repeatable flag is []string but an untyped array parameter is
     * []interface{}, and an object arrives as a JSON string that has to be
     * decoded. Go::getTypeName() is the authority on what the SDK declares, so
     * conversions are derived from it rather than guessed.
     *
     * @return array{
     *     package: string,
     *     method: string,
     *     optionType: string,
     *     decodes: list<array{var: string, source: string, helper: string, cleanup?: string}>,
     *     required: list<array{expression: string}>,
     *     optional: list<array{flag: string, setter: string, expression: string}>
     * }
     */
    protected function getGoCallPlan(array $method, array $service): array
    {
        // The SDK names these with `caseUcfirst`, which PascalCases through
        // camelCase -- `client_id` becomes `ClientId`, not `Client_id`.
        $methodName = $this->toPascalCase($method['name'] ?? '');
        $required = [];
        $optional = [];
        $decodes = [];

        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            $variable = $this->getGoVarName($parameter['name']);
            $flagType = $this->getGoCliOption($parameter)['goType'];
            $sdkType = parent::getTypeName($parameter);
            $expression = $variable;

            if ($this->isCliGraphQLInput($parameter, $method, $service)) {
                $expression = $variable . 'Value';
                $decodes[] = [
                    'var' => $expression,
                    'source' => $variable,
                    'helper' => 'GraphQLRequest',
                ];
            } elseif ($flagType !== $sdkType) {
                [$expression, $decode] = $this->convertToSdkType(
                    $variable,
                    $flagType,
                    $sdkType,
                    (bool) ($method['packaging'] ?? false),
                );

                if ($decode !== null) {
                    $decodes[] = $decode;
                }
            }

            if ($parameter['required'] ?? false) {
                $required[] = ['expression' => $expression];

                continue;
            }

            $optional[] = [
                'flag' => $this->getCliOptionName($parameter['name']),
                'setter' => 'With' . $methodName . $this->toPascalCase($parameter['name']),
                'expression' => $expression,
            ];
        }

        return [
            'package' => $this->getGoPackageName($service['name'] ?? ''),
            'method' => $methodName,
            'optionType' => $methodName . 'Option',
            'decodes' => $decodes,
            'required' => $required,
            'optional' => $optional,
        ];
    }

    /**
     * Bridge a flag's Go type to the type the SDK parameter declares.
     *
     * Returns the call-site expression, plus a statement to emit before the call
     * when the conversion can fail and its error has to surface.
     *
     * @return array{0: string, 1: array{var: string, source: string, helper: string, cleanup?: string}|null}
     */
    protected function convertToSdkType(
        string $variable,
        string $flagType,
        string $sdkType,
        bool $packaging = false,
    ): array {
        // A JSON string or a file path becomes a decoded value, and either can
        // fail on bad input, so both are decoded into their own variable first.
        if ($sdkType === 'interface{}' && $flagType === 'string') {
            return [$variable . 'Value', ['var' => $variable . 'Value', 'source' => $variable, 'helper' => 'JSONObject']];
        }
        if ($sdkType === 'file.InputFile') {
            $decode = [
                'var' => $variable . 'File',
                'source' => $variable,
                'helper' => $packaging ? 'DeploymentInputFile' : 'InputFile',
            ];
            if ($packaging) {
                $decode['cleanup'] = $variable . 'FileCleanup';
            }

            return [$variable . 'File', $decode];
        }

        // Slice parameters -- []interface{}, []float64, [][]interface{},
        // []map[string]any -- need each repetition parsed, because Go cannot
        // hand the API a JSON string where it declares an object or number.
        if (str_starts_with($sdkType, '[]') && $flagType === '[]string') {
            $element = substr($sdkType, 2);

            return [
                $variable . 'Decoded',
                [
                    'var' => $variable . 'Decoded',
                    'source' => $variable,
                    'helper' => 'DecodeSlice[' . $element . ']',
                ],
            ];
        }

        // Numeric widening and anything else the SDK spells differently but Go
        // can convert directly.
        if (in_array($sdkType, ['float64', 'int'], true) && in_array($flagType, ['float64', 'int'], true)) {
            return [$sdkType . '(' . $variable . ')', null];
        }

        return [$variable, null];
    }

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
            new TwigFilter('goPackage', fn (string $service): string => $this->getGoPackageName($service)),
            new TwigFilter('goString', fn (?string $value): string => $this->toGoString($value)),
        ]);
    }

    #[Override]
    public function getFunctions(): array
    {
        return array_merge($this->getCliHelpFunctions(), [
            new TwigFunction('getGoCliOption', fn (array $parameter): array => $this->getGoCliOption($parameter)),
            new TwigFunction('getGoVarName', fn (array $parameter): string => $this->getGoVarName($parameter['name'])),
            new TwigFunction('getGoCallPlan', fn (array $method, array $service): array => $this->getGoCallPlan($method, $service)),
        ]);
    }

    #[Override]
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'go.mod',
                'template'      => 'go-cli/go.mod.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'main.go',
                'template'      => 'go-cli/main.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'cli/README.md.twig',
            ],

            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => 'cli/LICENSE.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'cli/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'cli/docs/example.md.twig',
            ],

            // Shared verbatim with the TypeScript CLI. Both build their download
            // URL from `npmPackage`, which names every release asset, so the one
            // script serves whichever CLI is generated -- and a change to asset
            // naming cannot move one without the other. `curl | bash` has no
            // route to this binary without these two entries.
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
                'destination'   => '.gitignore',
                'template'      => 'go-cli/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitattributes',
                'template'      => 'go-cli/.gitattributes',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/root.go',
                'template'      => 'go-cli/internal/cmd/root.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/help.go',
                'template'      => 'go-cli/internal/cmd/help.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/help_test.go',
                'template'      => 'go-cli/internal/cmd/help_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/global.go',
                'template'      => 'go-cli/internal/config/global.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/home_native.go',
                'template'      => 'go-cli/internal/config/home_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/home_js.go',
                'template'      => 'go-cli/internal/config/home_js.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/write.go',
                'template'      => 'go-cli/internal/config/write.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/config_test.go',
                'template'      => 'go-cli/internal/config/config_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/redact.go',
                'template'      => 'go-cli/internal/output/redact.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/redact_test.go',
                'template'      => 'go-cli/internal/output/redact_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/client.go',
                'template'      => 'go-cli/internal/client/client.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/fetch_js.go',
                'template'      => 'go-cli/internal/client/fetch_js.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/fetch_native.go',
                'template'      => 'go-cli/internal/client/fetch_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/selfsigned_test.go',
                'template'      => 'go-cli/internal/client/selfsigned_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generic.go',
                'template'      => 'go-cli/internal/cmd/generic.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/session.go',
                'template'      => 'go-cli/internal/cmd/session.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/ambient_native.go',
                'template'      => 'go-cli/internal/cmd/ambient_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/ambient_js.go',
                'template'      => 'go-cli/internal/cmd/ambient_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/keyring_native.go',
                'template'      => 'go-cli/internal/auth/keyring_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/keyring_js.go',
                'template'      => 'go-cli/internal/auth/keyring_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/refresh.go',
                'template'      => 'go-cli/internal/auth/refresh.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/refresh_test.go',
                'template'      => 'go-cli/internal/auth/refresh_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/jsonx/object.go',
                'template'      => 'go-cli/internal/jsonx/object.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/jsonx/object_test.go',
                'template'      => 'go-cli/internal/jsonx/object_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/json.go',
                'template'      => 'go-cli/internal/config/json.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/json.go',
                'template'      => 'go-cli/internal/output/json.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/json_test.go',
                'template'      => 'go-cli/internal/output/json_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/local.go',
                'template'      => 'go-cli/internal/config/local.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/local_test.go',
                'template'      => 'go-cli/internal/config/local_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/device.go',
                'template'      => 'go-cli/internal/auth/device.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/device_test.go',
                'template'      => 'go-cli/internal/auth/device_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/login.go',
                'template'      => 'go-cli/internal/cmd/login.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/commands_portable.go',
                'template'      => 'go-cli/internal/cmd/commands_portable.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/commands_host.go',
                'template'      => 'go-cli/internal/cmd/commands_host.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/commands_host_stub.go',
                'template'      => 'go-cli/internal/cmd/commands_host_stub.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/endpoint.go',
                'template'      => 'go-cli/internal/cmd/endpoint.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/login_test.go',
                'template'      => 'go-cli/internal/cmd/login_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/shorthand_test.go',
                'template'      => 'go-cli/internal/cmd/shorthand_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/filter.go',
                'template'      => 'go-cli/internal/output/filter.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/filter_test.go',
                'template'      => 'go-cli/internal/output/filter_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/render.go',
                'template'      => 'go-cli/internal/output/render.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/sdk.go',
                'template'      => 'go-cli/internal/sdk/sdk.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/ambient_native.go',
                'template'      => 'go-cli/internal/sdk/ambient_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/ambient_js.go',
                'template'      => 'go-cli/internal/sdk/ambient_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/sdk_test.go',
                'template'      => 'go-cli/internal/sdk/sdk_test.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/query/query.go',
                'template'      => 'go-cli/internal/query/query.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/query/query_test.go',
                'template'      => 'go-cli/internal/query/query_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/globals.go',
                'template'      => 'go-cli/internal/app/globals.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/client.go',
                'template'      => 'go-cli/internal/app/client.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/flags.go',
                'template'      => 'go-cli/internal/app/flags.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/version.go',
                'template'      => 'go-cli/internal/app/version.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/version_test.go',
                'template'      => 'go-cli/internal/app/version_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/convert.go',
                'template'      => 'go-cli/internal/app/convert.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/fallback.go',
                'template'      => 'go-cli/internal/app/fallback.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/inputfile.go',
                'template'      => 'go-cli/internal/app/inputfile.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/inputfile_test.go',
                'template'      => 'go-cli/internal/app/inputfile_test.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/client.go',
                'template'      => 'go-cli/internal/cmd/client.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/completion.go',
                'template'      => 'go-cli/internal/cmd/completion.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/valueformat.go',
                'template'      => 'go-cli/internal/output/valueformat.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/update/check.go',
                'template'      => 'go-cli/internal/update/check.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/update/check_test.go',
                'template'      => 'go-cli/internal/update/check_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/sections.go',
                'template'      => 'go-cli/internal/output/sections.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/sections_test.go',
                'template'      => 'go-cli/internal/output/sections_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/collections.go',
                'template'      => 'go-cli/internal/output/collections.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/spinner.go',
                'template'      => 'go-cli/internal/output/spinner.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/spinner_test.go',
                'template'      => 'go-cli/internal/output/spinner_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runjwt.go',
                'template'      => 'go-cli/internal/cmd/runjwt.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runjwt_test.go',
                'template'      => 'go-cli/internal/cmd/runjwt_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runport_test.go',
                'template'      => 'go-cli/internal/cmd/runport_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/settings_test.go',
                'template'      => 'go-cli/internal/cmd/settings_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/fanout_test.go',
                'template'      => 'go-cli/internal/cmd/fanout_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/pullpushkeys_test.go',
                'template'      => 'go-cli/internal/cmd/pullpushkeys_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/flags.go',
                'template'      => 'go-cli/internal/cmd/flags.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/flags_test.go',
                'template'      => 'go-cli/internal/cmd/flags_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/deploylog.go',
                'template'      => 'go-cli/internal/output/deploylog.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/deploylog_test.go',
                'template'      => 'go-cli/internal/output/deploylog_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/ignore.go',
                'template'      => 'go-cli/internal/config/ignore.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/theme.go',
                'template'      => 'go-cli/internal/prompt/theme.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushselect_test.go',
                'template'      => 'go-cli/internal/cmd/pushselect_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/ignore_test.go',
                'template'      => 'go-cli/internal/config/ignore_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/valueformat_test.go',
                'template'      => 'go-cli/internal/output/valueformat_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/testdata/valueformat.json',
                'template'      => 'go-cli/internal/output/testdata/valueformat.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/completion_test.go',
                'template'      => 'go-cli/internal/cmd/completion_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/install.go',
                'template'      => 'go-cli/internal/app/install.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/updatecheck_native.go',
                'template'      => 'go-cli/internal/app/updatecheck_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/updatecheck_browser.go',
                'template'      => 'go-cli/internal/app/updatecheck_browser.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/update.go',
                'template'      => 'go-cli/internal/cmd/update.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/updateasset_test.go',
                'template'      => 'go-cli/internal/cmd/updateasset_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/reportargs_test.go',
                'template'      => 'go-cli/internal/cmd/reportargs_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/concurrency_test.go',
                'template'      => 'go-cli/internal/client/concurrency_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/types.go',
                'template'      => 'go-cli/internal/cmd/types.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generate.go',
                'template'      => 'go-cli/internal/cmd/generate.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/typegen_commands_test.go',
                'template'      => 'go-cli/internal/cmd/typegen_commands_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/message.go',
                'template'      => 'go-cli/internal/output/message.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/ignore.go',
                'template'      => 'go-cli/internal/ignore/ignore.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/ignore_test.go',
                'template'      => 'go-cli/internal/ignore/ignore_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/testdata/cases.json',
                'template'      => 'go-cli/internal/ignore/testdata/cases.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/dotenv.go',
                'template'      => 'go-cli/internal/dotenv/dotenv.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/dotenv_test.go',
                'template'      => 'go-cli/internal/dotenv/dotenv_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/testdata/cases.json',
                'template'      => 'go-cli/internal/dotenv/testdata/cases.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/function.go',
                'template'      => 'go-cli/internal/config/function.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/runtime.go',
                'template'      => 'go-cli/internal/docker/runtime.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/source.go',
                'template'      => 'go-cli/internal/docker/source.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/docker.go',
                'template'      => 'go-cli/internal/docker/docker.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/emulate.go',
                'template'      => 'go-cli/internal/docker/emulate.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/queue.go',
                'template'      => 'go-cli/internal/docker/queue.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/docker_test.go',
                'template'      => 'go-cli/internal/docker/docker_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/archive/targz.go',
                'template'      => 'go-cli/internal/archive/targz.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/archive/targz_test.go',
                'template'      => 'go-cli/internal/archive/targz_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/watch/watch.go',
                'template'      => 'go-cli/internal/watch/watch.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/prompt.go',
                'template'      => 'go-cli/internal/prompt/prompt.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/terminal.go',
                'template'      => 'go-cli/internal/prompt/terminal.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/prompt_test.go',
                'template'      => 'go-cli/internal/prompt/prompt_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/appwrite/id.go',
                'template'      => 'go-cli/internal/appwrite/id.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/resource.go',
                'template'      => 'go-cli/internal/config/resource.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/init.go',
                'template'      => 'go-cli/internal/cmd/init.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initproject.go',
                'template'      => 'go-cli/internal/cmd/initproject.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.goreleaser.yaml',
                'template'      => 'go-cli/.goreleaser.yaml.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/adhoc-sign.sh',
                'template'      => 'go-cli/scripts/adhoc-sign.sh',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scoop/appwrite.config.json',
                'template'      => 'go-cli/scoop/appwrite.config.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scripts/stage-assets.mjs',
                'template'      => 'go-cli/scripts/stage-assets.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scripts/build-npm-packages.mjs',
                'template'      => 'go-cli/scripts/build-npm-packages.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'go-cli/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'npm/package.json',
                'template'      => 'go-cli/npm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'npm/run.js',
                'template'      => 'go-cli/npm/run.js.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initscaffold.go',
                'template'      => 'go-cli/internal/cmd/initscaffold.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initscaffold_test.go',
                'template'      => 'go-cli/internal/cmd/initscaffold_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initfunction.go',
                'template'      => 'go-cli/internal/cmd/initfunction.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initsite.go',
                'template'      => 'go-cli/internal/cmd/initsite.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initskill.go',
                'template'      => 'go-cli/internal/cmd/initskill.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pull.go',
                'template'      => 'go-cli/internal/cmd/pull.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pulldatabase.go',
                'template'      => 'go-cli/internal/cmd/pulldatabase.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullsettings.go',
                'template'      => 'go-cli/internal/cmd/pullsettings.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/render_test.go',
                'template'      => 'go-cli/internal/app/render_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/env.go',
                'template'      => 'go-cli/internal/sdk/env.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/capture.go',
                'template'      => 'go-cli/internal/sdk/capture.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/capture_test.go',
                'template'      => 'go-cli/internal/sdk/capture_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generate_test.go',
                'template'      => 'go-cli/internal/cmd/generate_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/logout.go',
                'template'      => 'go-cli/internal/cmd/logout.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/logout_test.go',
                'template'      => 'go-cli/internal/cmd/logout_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/errors.go',
                'template'      => 'go-cli/internal/cmd/errors.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/errors_test.go',
                'template'      => 'go-cli/internal/cmd/errors_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/client_test.go',
                'template'      => 'go-cli/internal/cmd/client_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/organization.go',
                'template'      => 'go-cli/internal/cmd/organization.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/organization_test.go',
                'template'      => 'go-cli/internal/cmd/organization_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullfunction.go',
                'template'      => 'go-cli/internal/cmd/pullfunction.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullall.go',
                'template'      => 'go-cli/internal/cmd/pullall.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushcommon.go',
                'template'      => 'go-cli/internal/cmd/pushcommon.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushsimple.go',
                'template'      => 'go-cli/internal/cmd/pushsimple.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/resource.go',
                'template'      => 'go-cli/internal/cmd/resource.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushtally_test.go',
                'template'      => 'go-cli/internal/cmd/pushtally_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushall.go',
                'template'      => 'go-cli/internal/cmd/pushall.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushdatabase.go',
                'template'      => 'go-cli/internal/cmd/pushdatabase.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushdeploy.go',
                'template'      => 'go-cli/internal/cmd/pushdeploy.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/pushreport_test.go',
                'template'      => 'go-cli/internal/cmd/pushreport_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/inittemplateenv_test.go',
                'template'      => 'go-cli/internal/cmd/inittemplateenv_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initprojectskills_test.go',
                'template'      => 'go-cli/internal/cmd/initprojectskills_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/errordetail_test.go',
                'template'      => 'go-cli/internal/cmd/errordetail_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initprojectskills.go',
                'template'      => 'go-cli/internal/cmd/initprojectskills.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/keyringbackend_test.go',
                'template'      => 'go-cli/internal/auth/keyringbackend_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/store_test.go',
                'template'      => 'go-cli/internal/auth/store_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/apierror_test.go',
                'template'      => 'go-cli/internal/client/apierror_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/loginswitch_test.go',
                'template'      => 'go-cli/internal/cmd/loginswitch_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushpreview.go',
                'template'      => 'go-cli/internal/cmd/pushpreview.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushpreview_test.go',
                'template'      => 'go-cli/internal/cmd/pushpreview_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/render.go',
                'template'      => 'go-cli/internal/preview/render.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/render_test.go',
                'template'      => 'go-cli/internal/preview/render_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/frame.go',
                'template'      => 'go-cli/internal/preview/frame.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/frame_test.go',
                'template'      => 'go-cli/internal/preview/frame_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/deploy/deploy.go',
                'template'      => 'go-cli/internal/deploy/deploy.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/deploy/deploy_test.go',
                'template'      => 'go-cli/internal/deploy/deploy_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/attributes.go',
                'template'      => 'go-cli/internal/schema/attributes.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/attributes_test.go',
                'template'      => 'go-cli/internal/schema/attributes_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/database.go',
                'template'      => 'go-cli/internal/schema/database.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/operations.go',
                'template'      => 'go-cli/internal/schema/operations.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/poll.go',
                'template'      => 'go-cli/internal/schema/poll.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/render.go',
                'template'      => 'go-cli/internal/schema/render.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/value.go',
                'template'      => 'go-cli/internal/schema/value.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/client/paginate.go',
                'template'      => 'go-cli/internal/client/paginate.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/client/paginate_test.go',
                'template'      => 'go-cli/internal/client/paginate_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/appwrite/id_test.go',
                'template'      => 'go-cli/internal/appwrite/id_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/resource_test.go',
                'template'      => 'go-cli/internal/config/resource_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/run.go',
                'template'      => 'go-cli/internal/cmd/run.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/install_test.go',
                'template'      => 'go-cli/internal/app/install_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/handlebars.go',
                'template'      => 'go-cli/internal/typegen/handlebars.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/handlebars_test.go',
                'template'      => 'go-cli/internal/typegen/handlebars_test.go',
            ],
            // The typegen templates are sourced from the TypeScript CLI's own
            // template directory rather than copied into templates/go-cli. One
            // source, two outputs: the alternative is two .hbs copies that
            // drift, which is exactly what internal/typegen exists to avoid.
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/constants.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/constants.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/databases.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/databases.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/index.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/index.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/types.ts.hbs',
                'template'      => 'cli/lib/commands/generators/typescript/templates/types.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates.go',
                'template'      => 'go-cli/internal/typegen/templates.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates_test.go',
                'template'      => 'go-cli/internal/typegen/templates_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/casing.go',
                'template'      => 'go-cli/internal/typegen/casing.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/casing_test.go',
                'template'      => 'go-cli/internal/typegen/casing_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/language.go',
                'template'      => 'go-cli/internal/typegen/language.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/typegen/typescript.go',
                'template'      => 'go-cli/internal/typegen/typescript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/javascript.go',
                'template'      => 'go-cli/internal/typegen/javascript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/language_test.go',
                'template'      => 'go-cli/internal/typegen/language_test.go',
            ],
            // Baselines captured by running the TypeScript emitters under node.
            // Copied rather than rendered: a Twig pass over them would treat
            // their braces as template syntax.
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/collections.json',
                'template'      => 'go-cli/internal/typegen/testdata/collections.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/ts.loose.appwrite.d.ts',
                'template'      => 'go-cli/internal/typegen/testdata/ts.loose.appwrite.d.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/ts.strict.appwrite.d.ts',
                'template'      => 'go-cli/internal/typegen/testdata/ts.strict.appwrite.d.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/js.loose.appwrite-types.js',
                'template'      => 'go-cli/internal/typegen/testdata/js.loose.appwrite-types.js',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/js.strict.appwrite-types.js',
                'template'      => 'go-cli/internal/typegen/testdata/js.strict.appwrite-types.js',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/php.go',
                'template'      => 'go-cli/internal/typegen/php.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/kotlin.go',
                'template'      => 'go-cli/internal/typegen/kotlin.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.loose.Authors.php',
                'template'      => 'go-cli/internal/typegen/testdata/php.loose.Authors.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.loose.BooksZines.php',
                'template'      => 'go-cli/internal/typegen/testdata/php.loose.BooksZines.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.strict.Authors.php',
                'template'      => 'go-cli/internal/typegen/testdata/php.strict.Authors.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.strict.BooksZines.php',
                'template'      => 'go-cli/internal/typegen/testdata/php.strict.BooksZines.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.loose.Authors.kt',
                'template'      => 'go-cli/internal/typegen/testdata/kotlin.loose.Authors.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.loose.BooksZines.kt',
                'template'      => 'go-cli/internal/typegen/testdata/kotlin.loose.BooksZines.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.strict.Authors.kt',
                'template'      => 'go-cli/internal/typegen/testdata/kotlin.strict.Authors.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.strict.BooksZines.kt',
                'template'      => 'go-cli/internal/typegen/testdata/kotlin.strict.BooksZines.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/swift.go',
                'template'      => 'go-cli/internal/typegen/swift.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.loose.Authors.swift',
                'template'      => 'go-cli/internal/typegen/testdata/swift.loose.Authors.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.loose.BooksZines.swift',
                'template'      => 'go-cli/internal/typegen/testdata/swift.loose.BooksZines.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.strict.Authors.swift',
                'template'      => 'go-cli/internal/typegen/testdata/swift.strict.Authors.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.strict.BooksZines.swift',
                'template'      => 'go-cli/internal/typegen/testdata/swift.strict.BooksZines.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/java.go',
                'template'      => 'go-cli/internal/typegen/java.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.loose.Authors.java',
                'template'      => 'go-cli/internal/typegen/testdata/java.loose.Authors.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.loose.BooksZines.java',
                'template'      => 'go-cli/internal/typegen/testdata/java.loose.BooksZines.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.strict.Authors.java',
                'template'      => 'go-cli/internal/typegen/testdata/java.strict.Authors.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.strict.BooksZines.java',
                'template'      => 'go-cli/internal/typegen/testdata/java.strict.BooksZines.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/dart.go',
                'template'      => 'go-cli/internal/typegen/dart.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/csharp.go',
                'template'      => 'go-cli/internal/typegen/csharp.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.loose.authors.dart',
                'template'      => 'go-cli/internal/typegen/testdata/dart.loose.authors.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.loose.books_zines.dart',
                'template'      => 'go-cli/internal/typegen/testdata/dart.loose.books_zines.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.strict.authors.dart',
                'template'      => 'go-cli/internal/typegen/testdata/dart.strict.authors.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.strict.books_zines.dart',
                'template'      => 'go-cli/internal/typegen/testdata/dart.strict.books_zines.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.loose.Authors.cs',
                'template'      => 'go-cli/internal/typegen/testdata/cs.loose.Authors.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.loose.BooksZines.cs',
                'template'      => 'go-cli/internal/typegen/testdata/cs.loose.BooksZines.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.strict.Authors.cs',
                'template'      => 'go-cli/internal/typegen/testdata/cs.strict.Authors.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.strict.BooksZines.cs',
                'template'      => 'go-cli/internal/typegen/testdata/cs.strict.BooksZines.cs',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/generator/generator.go',
                'template'      => 'go-cli/internal/generator/generator.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/detector.go',
                'template'      => 'go-cli/internal/generator/detector.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/generator/typescript.go',
                'template'      => 'go-cli/internal/generator/typescript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/generator_test.go',
                'template'      => 'go-cli/internal/generator/generator_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/config.json',
                'template'      => 'go-cli/internal/generator/testdata/config.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.databases.ts',
                'template'      => 'go-cli/internal/generator/testdata/server.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.types.ts',
                'template'      => 'go-cli/internal/generator/testdata/server.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.index.ts',
                'template'      => 'go-cli/internal/generator/testdata/server.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.constants.ts',
                'template'      => 'go-cli/internal/generator/testdata/server.constants.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.databases.ts',
                'template'      => 'go-cli/internal/generator/testdata/client.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.types.ts',
                'template'      => 'go-cli/internal/generator/testdata/client.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.index.ts',
                'template'      => 'go-cli/internal/generator/testdata/client.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.constants.ts',
                'template'      => 'go-cli/internal/generator/testdata/client.constants.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.databases.ts',
                'template'      => 'go-cli/internal/generator/testdata/empty.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.types.ts',
                'template'      => 'go-cli/internal/generator/testdata/empty.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.index.ts',
                'template'      => 'go-cli/internal/generator/testdata/empty.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.constants.ts',
                'template'      => 'go-cli/internal/generator/testdata/empty.constants.ts',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/services/register.go',
                'template'      => 'go-cli/internal/cmd/services/register.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'internal/cmd/services/{{ service.name | caseLower }}.go',
                'template'      => 'go-cli/internal/cmd/services/service.go.twig',
            ],
        ];
    }
}
