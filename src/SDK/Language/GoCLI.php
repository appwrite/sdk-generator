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
 *
 * See docs/go-cli/PLAN.md for the phased plan and the invariants this has to
 * preserve.
 */
class GoCLI extends Go
{
    use CliCommandSurface;

    /**
     * Go module path the CLI is published under.
     *
     * A constant rather than a settable param: Go has no relative imports, so
     * this string is baked into every import in the runtime packages. Those
     * files are `copy` scope -- plain Go that can be edited, vetted and tested
     * in place -- which is only safe while the path cannot change underneath
     * them.
     */
    public const string MODULE_PATH = 'github.com/appwrite/appwrite-cli-go';

    /**
     * @var array
     */
    #[Override]
    protected $params = [
        'modulePath' => self::MODULE_PATH,
        'executableName' => 'appwrite',
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
            self::TYPE_INTEGER, self::TYPE_NUMBER => ['Int', 'int', null],
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
     * Expression passed to the SDK call for one parameter.
     */
    protected function getGoCliArgExpression(array $parameter): string
    {
        $var = $this->getGoVarName($parameter['name']);

        return match ($parameter['type'] ?? self::TYPE_STRING) {
            self::TYPE_OBJECT => "parseJSONObject({$var})",
            self::TYPE_FILE => "openInputFile({$var})",
            default => $var,
        };
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
            new TwigFilter('goPackage', fn (string $service): string => $this->getGoPackageName($service)),
            new TwigFilter('goString', fn (?string $value): string => $this->toGoString($value)),
        ]);
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getGoCliOption', fn (array $parameter): array => $this->getGoCliOption($parameter)),
            new TwigFunction('getGoVarName', fn (array $parameter): string => $this->getGoVarName($parameter['name'])),
            new TwigFunction('getGoCliArgExpression', fn (array $parameter): string => $this->getGoCliArgExpression($parameter)),
        ];
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
                'template'      => 'go-cli/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.gitignore',
                'template'      => 'go-cli/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'internal/cmd/root.go',
                'template'      => 'go-cli/internal/cmd/root.go.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/global.go',
                'template'      => 'go-cli/internal/config/global.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/config_test.go',
                'template'      => 'go-cli/internal/config/config_test.go',
            ],
            [
                'scope'         => 'copy',
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
                'destination'   => 'internal/cmd/session.go',
                'template'      => 'go-cli/internal/cmd/session.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/keyring.go',
                'template'      => 'go-cli/internal/auth/keyring.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/refresh.go',
                'template'      => 'go-cli/internal/auth/refresh.go',
            ],
            [
                'scope'         => 'copy',
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
                'scope'         => 'copy',
                'destination'   => 'internal/config/json.go',
                'template'      => 'go-cli/internal/config/json.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/json.go',
                'template'      => 'go-cli/internal/output/json.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/output/json_test.go',
                'template'      => 'go-cli/internal/output/json_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/local.go',
                'template'      => 'go-cli/internal/config/local.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/config/local_test.go',
                'template'      => 'go-cli/internal/config/local_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/device.go',
                'template'      => 'go-cli/internal/auth/device.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/auth/device_test.go',
                'template'      => 'go-cli/internal/auth/device_test.go',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'internal/cmd/login.go',
                'template'      => 'go-cli/internal/cmd/login.go',
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
