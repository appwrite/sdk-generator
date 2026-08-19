<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language\Concern\CliCommandSurface;
use Override;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Tag;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Appwrite CLI generator.
 *
 * The generated CLI is implemented in Go. CLI-specific command analysis lives
 * in CliCommandSurface, while Go supplies type mapping, keywords, and comments.
 */
class CLI extends Go
{
    use CliCommandSurface;

    /**
     * Module the CLI imports the generated SDK from. Carries the /vN suffix Go
     * requires from v2 on, so it moves in lockstep with the version pinned in
     * templates/cli/go.mod.twig.
     */
    public const string SDK_MODULE = 'github.com/appwrite/sdk-for-go/v7';

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
        // ASCII art above the main help screen. Templates escape it for their
        // target syntax rather than sharing one encoded representation.
        'logo' => '',
        // The same art indented for install.sh and install.ps1.
        'logoUnescaped' => '',
        'homebrewTapOwner' => 'appwrite',
        'homebrewTapName' => 'appwrite',
        'homebrewTapBranch' => 'main',
    ];

    #[Override]
    public function getName(): string
    {
        return 'cli';
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
     * list shapes the user-visible flag name, while this one only shapes a
     * private Go identifier.
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
     * Maps the public flag contract to pflag's typed registration calls.
     *
     * @return array{flag: string, var: string, register: string, goType: string, required: bool, noOptDefault: string|null}
     */
    protected function getCliOption(Parameter $parameter): array
    {
        $flag = $this->getCliOptionName($parameter->name);
        $type = $this->getSchemaType($parameter);
        $required = $parameter->required;

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
            'var' => $this->getGoVarName($parameter->name),
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
    protected function getGoCallPlan(Operation $method, Tag $service): array
    {
        $methodName = $this->toPascalCase($this->getMethodName($method));
        $required = [];
        $optional = [];
        $decodes = [];

        foreach ($this->getOperationParameters($method) as $parameter) {
            $variable = $this->getGoVarName($parameter->name);
            $flagType = $this->getCliOption($parameter)['goType'];
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
                    (bool) ($method->extensions['x-appwrite']['packaging'] ?? false),
                );

                if ($decode !== null) {
                    $decodes[] = $decode;
                }
            }

            if ($parameter->required) {
                $required[] = ['expression' => $expression];

                continue;
            }

            $optional[] = [
                'flag' => $this->getCliOptionName($parameter->name),
                'setter' => 'With' . $methodName . $this->toPascalCase($parameter->name),
                'expression' => $expression,
            ];
        }

        return [
            'package' => $this->getGoPackageName($service->name),
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
            new TwigFilter('hasCliQueryParam', fn(array $methods): bool => $this->hasCliQueryParam($methods)),
            new TwigFilter('cliServiceScope', fn(?string $resourceHeader): ?array => $this->getCliServiceScope($resourceHeader)),
            new TwigFilter('cliQueryConfig', fn(Operation $method): array => $this->getCliQueryConfig($method)),
            new TwigFilter('cliTopLevelAliases', fn(Tag $service, array $methods): array => $this->getCliTopLevelAliases($service, $methods)),
            new TwigFilter('cliCommandTargets', fn(Operation $method, Tag $service): array => $this->getCliCommandTargets($method, $service)),
            new TwigFilter('cliFallbackHelpers', fn(Tag $service, array $methods): array => $this->getCliFallbackHelpers($service, $methods)),
            new TwigFilter('cliIsTopLevelAlias', fn(Operation $method, Tag $service): bool => $this->isCliTopLevelAlias($method, $service)),
            new TwigFilter('goPackage', fn (string $service): string => $this->getGoPackageName($service)),
            new TwigFilter('goString', fn (?string $value): string => $this->toGoString($value)),
        ]);
    }

    #[Override]
    public function getFunctions(): array
    {
        return array_merge($this->getCliHelpFunctions(), [
            new TwigFunction('getCliOption', fn(Parameter $parameter): array => $this->getCliOption($parameter)),
            new TwigFunction('getGoVarName', fn(Parameter $parameter): string => $this->getGoVarName($parameter->name)),
            new TwigFunction('getGoCallPlan', fn(Operation $method, Tag $service): array => $this->getGoCallPlan($method, $service)),
        ]);
    }

    #[Override]
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'go.mod',
                'template'      => 'cli/go.mod.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'main.go',
                'template'      => 'cli/main.go.twig',
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
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'cli/docs/example.md.twig',
            ],

            // Both installers derive their download URL from `npmPackage`, which
            // also names every release asset. `curl | bash` has no route to the
            // binary without these entries.
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
                'template'      => 'cli/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitattributes',
                'template'      => 'cli/.gitattributes',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/root.go',
                'template'      => 'cli/internal/cmd/root.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/help.go',
                'template'      => 'cli/internal/cmd/help.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/help_test.go',
                'template'      => 'cli/internal/cmd/help_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/global.go',
                'template'      => 'cli/internal/config/global.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/home_native.go',
                'template'      => 'cli/internal/config/home_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/home_js.go',
                'template'      => 'cli/internal/config/home_js.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/write.go',
                'template'      => 'cli/internal/config/write.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/config_test.go',
                'template'      => 'cli/internal/config/config_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/redact.go',
                'template'      => 'cli/internal/output/redact.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/redact_test.go',
                'template'      => 'cli/internal/output/redact_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/client.go',
                'template'      => 'cli/internal/client/client.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/fetch_js.go',
                'template'      => 'cli/internal/client/fetch_js.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/fetch_native.go',
                'template'      => 'cli/internal/client/fetch_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/selfsigned_test.go',
                'template'      => 'cli/internal/client/selfsigned_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generic.go',
                'template'      => 'cli/internal/cmd/generic.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/session.go',
                'template'      => 'cli/internal/cmd/session.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/ambient_native.go',
                'template'      => 'cli/internal/cmd/ambient_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/ambient_js.go',
                'template'      => 'cli/internal/cmd/ambient_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/keyring_native.go',
                'template'      => 'cli/internal/auth/keyring_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/keyring_js.go',
                'template'      => 'cli/internal/auth/keyring_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/refresh.go',
                'template'      => 'cli/internal/auth/refresh.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/refresh_test.go',
                'template'      => 'cli/internal/auth/refresh_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/jsonx/object.go',
                'template'      => 'cli/internal/jsonx/object.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/jsonx/object_test.go',
                'template'      => 'cli/internal/jsonx/object_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/json.go',
                'template'      => 'cli/internal/config/json.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/json.go',
                'template'      => 'cli/internal/output/json.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/json_test.go',
                'template'      => 'cli/internal/output/json_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/local.go',
                'template'      => 'cli/internal/config/local.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/local_test.go',
                'template'      => 'cli/internal/config/local_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/auth/device.go',
                'template'      => 'cli/internal/auth/device.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/device_test.go',
                'template'      => 'cli/internal/auth/device_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/login.go',
                'template'      => 'cli/internal/cmd/login.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/commands_portable.go',
                'template'      => 'cli/internal/cmd/commands_portable.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/commands_host.go',
                'template'      => 'cli/internal/cmd/commands_host.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/commands_host_stub.go',
                'template'      => 'cli/internal/cmd/commands_host_stub.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/endpoint.go',
                'template'      => 'cli/internal/cmd/endpoint.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/login_test.go',
                'template'      => 'cli/internal/cmd/login_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/shorthand_test.go',
                'template'      => 'cli/internal/cmd/shorthand_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/filter.go',
                'template'      => 'cli/internal/output/filter.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/filter_test.go',
                'template'      => 'cli/internal/output/filter_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/render.go',
                'template'      => 'cli/internal/output/render.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/sdk.go',
                'template'      => 'cli/internal/sdk/sdk.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/ambient_native.go',
                'template'      => 'cli/internal/sdk/ambient_native.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/ambient_js.go',
                'template'      => 'cli/internal/sdk/ambient_js.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/sdk/sdk_test.go',
                'template'      => 'cli/internal/sdk/sdk_test.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/query/query.go',
                'template'      => 'cli/internal/query/query.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/query/query_test.go',
                'template'      => 'cli/internal/query/query_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/globals.go',
                'template'      => 'cli/internal/app/globals.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/client.go',
                'template'      => 'cli/internal/app/client.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/flags.go',
                'template'      => 'cli/internal/app/flags.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/flags_test.go',
                'template'      => 'cli/internal/app/flags_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/version.go',
                'template'      => 'cli/internal/app/version.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/version_test.go',
                'template'      => 'cli/internal/app/version_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/convert.go',
                'template'      => 'cli/internal/app/convert.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/fallback.go',
                'template'      => 'cli/internal/app/fallback.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/fallback_test.go',
                'template'      => 'cli/internal/app/fallback_test.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/inputfile.go',
                'template'      => 'cli/internal/app/inputfile.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/inputfile_test.go',
                'template'      => 'cli/internal/app/inputfile_test.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/client.go',
                'template'      => 'cli/internal/cmd/client.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/completion.go',
                'template'      => 'cli/internal/cmd/completion.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/valueformat.go',
                'template'      => 'cli/internal/output/valueformat.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/update/check.go',
                'template'      => 'cli/internal/update/check.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/update/check_test.go',
                'template'      => 'cli/internal/update/check_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/sections.go',
                'template'      => 'cli/internal/output/sections.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/sections_test.go',
                'template'      => 'cli/internal/output/sections_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/output/collections.go',
                'template'      => 'cli/internal/output/collections.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/spinner.go',
                'template'      => 'cli/internal/output/spinner.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/spinner_test.go',
                'template'      => 'cli/internal/output/spinner_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runjwt.go',
                'template'      => 'cli/internal/cmd/runjwt.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runjwt_test.go',
                'template'      => 'cli/internal/cmd/runjwt_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/runport_test.go',
                'template'      => 'cli/internal/cmd/runport_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/settings_test.go',
                'template'      => 'cli/internal/cmd/settings_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/fanout_test.go',
                'template'      => 'cli/internal/cmd/fanout_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/pullpushkeys_test.go',
                'template'      => 'cli/internal/cmd/pullpushkeys_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/flags.go',
                'template'      => 'cli/internal/cmd/flags.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/flags_test.go',
                'template'      => 'cli/internal/cmd/flags_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/deploylog.go',
                'template'      => 'cli/internal/output/deploylog.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/deploylog_test.go',
                'template'      => 'cli/internal/output/deploylog_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/ignore.go',
                'template'      => 'cli/internal/config/ignore.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/theme.go',
                'template'      => 'cli/internal/prompt/theme.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushselect_test.go',
                'template'      => 'cli/internal/cmd/pushselect_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/ignore_test.go',
                'template'      => 'cli/internal/config/ignore_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/valueformat_test.go',
                'template'      => 'cli/internal/output/valueformat_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/testdata/valueformat.json',
                'template'      => 'cli/internal/output/testdata/valueformat.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/completion_test.go',
                'template'      => 'cli/internal/cmd/completion_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/install.go',
                'template'      => 'cli/internal/app/install.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/updatecheck_native.go',
                'template'      => 'cli/internal/app/updatecheck_native.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/updatecheck_browser.go',
                'template'      => 'cli/internal/app/updatecheck_browser.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/update.go',
                'template'      => 'cli/internal/cmd/update.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/updateasset_test.go',
                'template'      => 'cli/internal/cmd/updateasset_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/reportargs_test.go',
                'template'      => 'cli/internal/cmd/reportargs_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/concurrency_test.go',
                'template'      => 'cli/internal/client/concurrency_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/types.go',
                'template'      => 'cli/internal/cmd/types.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generate.go',
                'template'      => 'cli/internal/cmd/generate.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/typegen_commands_test.go',
                'template'      => 'cli/internal/cmd/typegen_commands_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/message.go',
                'template'      => 'cli/internal/output/message.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/ignore.go',
                'template'      => 'cli/internal/ignore/ignore.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/ignore_test.go',
                'template'      => 'cli/internal/ignore/ignore_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/ignore/testdata/cases.json',
                'template'      => 'cli/internal/ignore/testdata/cases.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/dotenv.go',
                'template'      => 'cli/internal/dotenv/dotenv.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/dotenv_test.go',
                'template'      => 'cli/internal/dotenv/dotenv_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/dotenv/testdata/cases.json',
                'template'      => 'cli/internal/dotenv/testdata/cases.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/function.go',
                'template'      => 'cli/internal/config/function.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/runtime.go',
                'template'      => 'cli/internal/docker/runtime.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/source.go',
                'template'      => 'cli/internal/docker/source.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/docker.go',
                'template'      => 'cli/internal/docker/docker.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/emulate.go',
                'template'      => 'cli/internal/docker/emulate.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/logs.go',
                'template'      => 'cli/internal/docker/logs.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/logs_test.go',
                'template'      => 'cli/internal/docker/logs_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/docker/queue.go',
                'template'      => 'cli/internal/docker/queue.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/docker/docker_test.go',
                'template'      => 'cli/internal/docker/docker_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/archive/targz.go',
                'template'      => 'cli/internal/archive/targz.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/archive/targz_test.go',
                'template'      => 'cli/internal/archive/targz_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/watch/watch.go',
                'template'      => 'cli/internal/watch/watch.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/prompt.go',
                'template'      => 'cli/internal/prompt/prompt.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/terminal.go',
                'template'      => 'cli/internal/prompt/terminal.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/prompt/prompt_test.go',
                'template'      => 'cli/internal/prompt/prompt_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/appwrite/id.go',
                'template'      => 'cli/internal/appwrite/id.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/config/resource.go',
                'template'      => 'cli/internal/config/resource.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/init.go',
                'template'      => 'cli/internal/cmd/init.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initproject.go',
                'template'      => 'cli/internal/cmd/initproject.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.goreleaser.yaml',
                'template'      => 'cli/.goreleaser.yaml.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/adhoc-sign.sh',
                'template'      => 'cli/scripts/adhoc-sign.sh',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scoop/appwrite.config.json',
                'template'      => 'cli/scoop/appwrite.config.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scripts/stage-assets.mjs',
                'template'      => 'cli/scripts/stage-assets.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'scripts/build-npm-packages.mjs',
                'template'      => 'cli/scripts/build-npm-packages.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'cli/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'npm/package.json',
                'template'      => 'cli/npm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'npm/run.js',
                'template'      => 'cli/npm/run.js.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initscaffold.go',
                'template'      => 'cli/internal/cmd/initscaffold.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initscaffold_test.go',
                'template'      => 'cli/internal/cmd/initscaffold_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initfunction.go',
                'template'      => 'cli/internal/cmd/initfunction.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initfunction_test.go',
                'template'      => 'cli/internal/cmd/initfunction_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initsite.go',
                'template'      => 'cli/internal/cmd/initsite.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initskill.go',
                'template'      => 'cli/internal/cmd/initskill.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pull.go',
                'template'      => 'cli/internal/cmd/pull.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pulldatabase.go',
                'template'      => 'cli/internal/cmd/pulldatabase.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullsettings.go',
                'template'      => 'cli/internal/cmd/pullsettings.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/app/render_test.go',
                'template'      => 'cli/internal/app/render_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/env.go',
                'template'      => 'cli/internal/sdk/env.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/capture.go',
                'template'      => 'cli/internal/sdk/capture.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/sdk/capture_test.go',
                'template'      => 'cli/internal/sdk/capture_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/generate_test.go',
                'template'      => 'cli/internal/cmd/generate_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/logout.go',
                'template'      => 'cli/internal/cmd/logout.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/logout_test.go',
                'template'      => 'cli/internal/cmd/logout_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/errors.go',
                'template'      => 'cli/internal/cmd/errors.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/errors_test.go',
                'template'      => 'cli/internal/cmd/errors_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/client_test.go',
                'template'      => 'cli/internal/cmd/client_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/organization.go',
                'template'      => 'cli/internal/cmd/organization.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/organization_test.go',
                'template'      => 'cli/internal/cmd/organization_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullfunction.go',
                'template'      => 'cli/internal/cmd/pullfunction.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pullall.go',
                'template'      => 'cli/internal/cmd/pullall.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushcommon.go',
                'template'      => 'cli/internal/cmd/pushcommon.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushsimple.go',
                'template'      => 'cli/internal/cmd/pushsimple.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/resource.go',
                'template'      => 'cli/internal/cmd/resource.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushtally_test.go',
                'template'      => 'cli/internal/cmd/pushtally_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushvariables_test.go',
                'template'      => 'cli/internal/cmd/pushvariables_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushall.go',
                'template'      => 'cli/internal/cmd/pushall.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushdatabase.go',
                'template'      => 'cli/internal/cmd/pushdatabase.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushdeploy.go',
                'template'      => 'cli/internal/cmd/pushdeploy.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/pushreport_test.go',
                'template'      => 'cli/internal/cmd/pushreport_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/inittemplateenv_test.go',
                'template'      => 'cli/internal/cmd/inittemplateenv_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/initprojectskills_test.go',
                'template'      => 'cli/internal/cmd/initprojectskills_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/errordetail_test.go',
                'template'      => 'cli/internal/cmd/errordetail_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/initprojectskills.go',
                'template'      => 'cli/internal/cmd/initprojectskills.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/keyringbackend_test.go',
                'template'      => 'cli/internal/auth/keyringbackend_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/store_test.go',
                'template'      => 'cli/internal/auth/store_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/client/apierror_test.go',
                'template'      => 'cli/internal/client/apierror_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/loginswitch_test.go',
                'template'      => 'cli/internal/cmd/loginswitch_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushpreview.go',
                'template'      => 'cli/internal/cmd/pushpreview.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/pushpreview_test.go',
                'template'      => 'cli/internal/cmd/pushpreview_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/render.go',
                'template'      => 'cli/internal/preview/render.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/render_test.go',
                'template'      => 'cli/internal/preview/render_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/frame.go',
                'template'      => 'cli/internal/preview/frame.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/preview/frame_test.go',
                'template'      => 'cli/internal/preview/frame_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/deploy/deploy.go',
                'template'      => 'cli/internal/deploy/deploy.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/deploy/deploy_test.go',
                'template'      => 'cli/internal/deploy/deploy_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/attributes.go',
                'template'      => 'cli/internal/schema/attributes.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/attributes_test.go',
                'template'      => 'cli/internal/schema/attributes_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/database.go',
                'template'      => 'cli/internal/schema/database.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/operations.go',
                'template'      => 'cli/internal/schema/operations.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/poll.go',
                'template'      => 'cli/internal/schema/poll.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/render.go',
                'template'      => 'cli/internal/schema/render.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/schema/value.go',
                'template'      => 'cli/internal/schema/value.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/client/paginate.go',
                'template'      => 'cli/internal/client/paginate.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/client/paginate_test.go',
                'template'      => 'cli/internal/client/paginate_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/appwrite/id_test.go',
                'template'      => 'cli/internal/appwrite/id_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/resource_test.go',
                'template'      => 'cli/internal/config/resource_test.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/run.go',
                'template'      => 'cli/internal/cmd/run.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/app/install_test.go',
                'template'      => 'cli/internal/app/install_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/handlebars.go',
                'template'      => 'cli/internal/typegen/handlebars.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/handlebars_test.go',
                'template'      => 'cli/internal/typegen/handlebars_test.go',
            ],
            // The embedded templates are the source for the TypeScript files
            // emitted by `appwrite generate`.
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/constants.ts.hbs',
                'template'      => 'cli/internal/typegen/templates/constants.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/databases.ts.hbs',
                'template'      => 'cli/internal/typegen/templates/databases.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/index.ts.hbs',
                'template'      => 'cli/internal/typegen/templates/index.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates/types.ts.hbs',
                'template'      => 'cli/internal/typegen/templates/types.ts.hbs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates.go',
                'template'      => 'cli/internal/typegen/templates.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/templates_test.go',
                'template'      => 'cli/internal/typegen/templates_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/casing.go',
                'template'      => 'cli/internal/typegen/casing.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/casing_test.go',
                'template'      => 'cli/internal/typegen/casing_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/language.go',
                'template'      => 'cli/internal/typegen/language.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/typegen/typescript.go',
                'template'      => 'cli/internal/typegen/typescript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/javascript.go',
                'template'      => 'cli/internal/typegen/javascript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/language_test.go',
                'template'      => 'cli/internal/typegen/language_test.go',
            ],
            // Baselines captured by running the TypeScript emitters under node.
            // Copied rather than rendered: a Twig pass over them would treat
            // their braces as template syntax.
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/collections.json',
                'template'      => 'cli/internal/typegen/testdata/collections.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/ts.loose.appwrite.d.ts',
                'template'      => 'cli/internal/typegen/testdata/ts.loose.appwrite.d.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/ts.strict.appwrite.d.ts',
                'template'      => 'cli/internal/typegen/testdata/ts.strict.appwrite.d.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/js.loose.appwrite-types.js',
                'template'      => 'cli/internal/typegen/testdata/js.loose.appwrite-types.js',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/js.strict.appwrite-types.js',
                'template'      => 'cli/internal/typegen/testdata/js.strict.appwrite-types.js',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/php.go',
                'template'      => 'cli/internal/typegen/php.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/kotlin.go',
                'template'      => 'cli/internal/typegen/kotlin.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.loose.Authors.php',
                'template'      => 'cli/internal/typegen/testdata/php.loose.Authors.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.loose.BooksZines.php',
                'template'      => 'cli/internal/typegen/testdata/php.loose.BooksZines.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.strict.Authors.php',
                'template'      => 'cli/internal/typegen/testdata/php.strict.Authors.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/php.strict.BooksZines.php',
                'template'      => 'cli/internal/typegen/testdata/php.strict.BooksZines.php',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.loose.Authors.kt',
                'template'      => 'cli/internal/typegen/testdata/kotlin.loose.Authors.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.loose.BooksZines.kt',
                'template'      => 'cli/internal/typegen/testdata/kotlin.loose.BooksZines.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.strict.Authors.kt',
                'template'      => 'cli/internal/typegen/testdata/kotlin.strict.Authors.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/kotlin.strict.BooksZines.kt',
                'template'      => 'cli/internal/typegen/testdata/kotlin.strict.BooksZines.kt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/swift.go',
                'template'      => 'cli/internal/typegen/swift.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.loose.Authors.swift',
                'template'      => 'cli/internal/typegen/testdata/swift.loose.Authors.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.loose.BooksZines.swift',
                'template'      => 'cli/internal/typegen/testdata/swift.loose.BooksZines.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.strict.Authors.swift',
                'template'      => 'cli/internal/typegen/testdata/swift.strict.Authors.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/swift.strict.BooksZines.swift',
                'template'      => 'cli/internal/typegen/testdata/swift.strict.BooksZines.swift',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/java.go',
                'template'      => 'cli/internal/typegen/java.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.loose.Authors.java',
                'template'      => 'cli/internal/typegen/testdata/java.loose.Authors.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.loose.BooksZines.java',
                'template'      => 'cli/internal/typegen/testdata/java.loose.BooksZines.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.strict.Authors.java',
                'template'      => 'cli/internal/typegen/testdata/java.strict.Authors.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/java.strict.BooksZines.java',
                'template'      => 'cli/internal/typegen/testdata/java.strict.BooksZines.java',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/dart.go',
                'template'      => 'cli/internal/typegen/dart.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/csharp.go',
                'template'      => 'cli/internal/typegen/csharp.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.loose.authors.dart',
                'template'      => 'cli/internal/typegen/testdata/dart.loose.authors.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.loose.books_zines.dart',
                'template'      => 'cli/internal/typegen/testdata/dart.loose.books_zines.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.strict.authors.dart',
                'template'      => 'cli/internal/typegen/testdata/dart.strict.authors.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/dart.strict.books_zines.dart',
                'template'      => 'cli/internal/typegen/testdata/dart.strict.books_zines.dart',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.loose.Authors.cs',
                'template'      => 'cli/internal/typegen/testdata/cs.loose.Authors.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.loose.BooksZines.cs',
                'template'      => 'cli/internal/typegen/testdata/cs.loose.BooksZines.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.strict.Authors.cs',
                'template'      => 'cli/internal/typegen/testdata/cs.strict.Authors.cs',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/typegen/testdata/cs.strict.BooksZines.cs',
                'template'      => 'cli/internal/typegen/testdata/cs.strict.BooksZines.cs',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/generator/generator.go',
                'template'      => 'cli/internal/generator/generator.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/detector.go',
                'template'      => 'cli/internal/generator/detector.go',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/generator/typescript.go',
                'template'      => 'cli/internal/generator/typescript.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/generator_test.go',
                'template'      => 'cli/internal/generator/generator_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/config.json',
                'template'      => 'cli/internal/generator/testdata/config.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.databases.ts',
                'template'      => 'cli/internal/generator/testdata/server.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.types.ts',
                'template'      => 'cli/internal/generator/testdata/server.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.index.ts',
                'template'      => 'cli/internal/generator/testdata/server.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/server.constants.ts',
                'template'      => 'cli/internal/generator/testdata/server.constants.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.databases.ts',
                'template'      => 'cli/internal/generator/testdata/client.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.types.ts',
                'template'      => 'cli/internal/generator/testdata/client.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.index.ts',
                'template'      => 'cli/internal/generator/testdata/client.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/client.constants.ts',
                'template'      => 'cli/internal/generator/testdata/client.constants.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.databases.ts',
                'template'      => 'cli/internal/generator/testdata/empty.databases.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.types.ts',
                'template'      => 'cli/internal/generator/testdata/empty.types.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.index.ts',
                'template'      => 'cli/internal/generator/testdata/empty.index.ts',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/generator/testdata/empty.constants.ts',
                'template'      => 'cli/internal/generator/testdata/empty.constants.ts',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/services/register.go',
                'template'      => 'cli/internal/cmd/services/register.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'internal/cmd/services/{{ service.name | caseLower }}.go',
                'template'      => 'cli/internal/cmd/services/service.go.twig',
            ],
        ];
    }
}
