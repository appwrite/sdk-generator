<?php

declare(strict_types=1);

namespace Tests\Generation;

use Exception;
use Closure;
use Iterator;
use Appwrite\SDK\Language;
use Appwrite\SDK\Language\Android;
use Appwrite\SDK\Language\Apple;
use Appwrite\SDK\Language\ClaudePlugin;
use Appwrite\SDK\Language\CLI;
use Appwrite\SDK\Language\CodexPlugin;
use Appwrite\SDK\Language\CursorPlugin;
use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\DotNet;
use Appwrite\SDK\Language\Flutter;
use Appwrite\SDK\Language\Go;
use Appwrite\SDK\Language\GraphQL;
use Appwrite\SDK\Language\HTTP;
use Appwrite\SDK\Language\Kotlin;
use Appwrite\SDK\Language\Node;
use Appwrite\SDK\Language\PHP;
use Appwrite\SDK\Language\Python;
use Appwrite\SDK\Language\ReactNative;
use Appwrite\SDK\Language\REST;
use Appwrite\SDK\Language\Ruby;
use Appwrite\SDK\Language\Rust;
use Appwrite\SDK\Language\Skills;
use Appwrite\SDK\Language\Swift;
use Appwrite\SDK\Language\Unity;
use Appwrite\SDK\Language\Web;
use Appwrite\SDK\SDK;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Parser;
use UnexpectedValueException;

/**
 * What the generator emits, for every language and platform, from the shared
 * fixture. Nothing here is compiled or run: `tests/e2e/` proves the generated
 * SDKs behave, this suite proves the generated trees contain what they should
 * and nothing they should not.
 */
final class GenerationTest extends TestCase
{
    private const string FIXTURE = __DIR__ . '/../resources/spec-openapi3.json';

    private const string OUTPUT = __DIR__ . '/sdks';

    private const array PLATFORMS = ['client', 'server', 'console'];

    /**
     * Fixture tokens and the platforms whose generated tree may contain them.
     * An empty list means the token must never appear. Every token is a single
     * lowercase word so it survives each language's identifier casing.
     */
    private const array TOKENS = [
        'zzexcludedservice' => [],
        'zzexcludedpayload' => [],
        'zzexcludedresult' => [],
        'zzexcludedstatus' => [],
        'zzexcludedchild' => [],
        'zzexcludedchildstatus' => [],
        'zzexcludedmethodpayload' => [],
        'zzexcludedmethodresult' => [],
        'zzexcludedmethodstatus' => [],
        'zzconsoleonly' => ['console'],
        'zzplatformclientalias' => ['client'],
        'zzplatformserveralias' => ['server'],
        'zzserveronlyheader' => ['server'],
    ];

    /**
     * Targets that render no header setters or no alias descriptions, so the
     * tokens carried by those never appear in their trees on any platform.
     */
    private const array UNRENDERED = [
        'cli' => ['zzserveronlyheader'],
        'rest' => ['zzplatformclientalias', 'zzplatformserveralias', 'zzserveronlyheader'],
        'graphql' => ['zzplatformclientalias', 'zzplatformserveralias', 'zzserveronlyheader'],
    ];

    /**
     * Where each language declares the fixture enums, and the declaration each
     * kind of enum value takes: a titled `oneOf` branch, a value without a safe
     * identifier, and an annotated localized value.
     *
     * @var array<string, array{string, string, string, string, string, string}>
     */
    private const array ENUM_DECLARATIONS = [
        'web' => ['src/enums/webhook-event.ts', 'UserCreated', 'src/enums/localized-status.ts', 'Value1', 'src/enums/province-type.ts', 'Capital'],
        'node' => ['src/enums/webhook-event.ts', 'UserCreated', 'src/enums/localized-status.ts', 'Value1', 'src/enums/province-type.ts', 'Capital'],
        'react-native' => ['src/enums/webhook-event.ts', 'UserCreated', 'src/enums/localized-status.ts', 'Value1', 'src/enums/province-type.ts', 'Capital'],
        'php' => ['src/Appwrite/Enums/WebhookEvent.php', 'USERCREATED', 'src/Appwrite/Enums/LocalizedStatus.php', 'public static function VALUE1', 'src/Appwrite/Enums/ProvinceType.php', 'public static function CAPITAL'],
        'python' => ['appwrite/enums/webhook_event.py', 'USERCREATED = "user.created"', 'appwrite/enums/localized_status.py', 'VALUE1 = "រាជធានី"', 'appwrite/enums/province_type.py', 'CAPITAL = "រាជធានី"'],
        'ruby' => ['lib/appwrite/enums/webhook_event.rb', "USERCREATED = 'user.created'", 'lib/appwrite/enums/localized_status.rb', "VALUE1 = 'រាជធានី'", 'lib/appwrite/enums/province_type.rb', "CAPITAL = 'រាជធានី'"],
        'dart' => ['lib/src/enums/webhook_event.dart', 'static const String userCreated', 'lib/src/enums/localized_status.dart', 'value1(value:', 'lib/src/enums/province_type.dart', 'capital(value:'],
        'flutter' => ['lib/src/enums/webhook_event.dart', 'static const String userCreated', 'lib/src/enums/localized_status.dart', 'value1(value:', 'lib/src/enums/province_type.dart', 'capital(value:'],
        'kotlin' => ['src/main/kotlin/io/appwrite/enums/WebhookEvent.kt', 'const val USERCREATED', 'src/main/kotlin/io/appwrite/enums/LocalizedStatus.kt', 'VALUE1("', 'src/main/kotlin/io/appwrite/enums/ProvinceType.kt', 'CAPITAL("'],
        'android' => ['library/src/main/java/io/appwrite/enums/WebhookEvent.kt', 'const val USERCREATED', 'library/src/main/java/io/appwrite/enums/LocalizedStatus.kt', 'VALUE1("', 'library/src/main/java/io/appwrite/enums/ProvinceType.kt', 'CAPITAL("'],
        'swift' => ['Sources/AppwriteEnums/WebhookEvent.swift', 'public static let userCreated', 'Sources/AppwriteEnums/LocalizedStatus.swift', 'case value1', 'Sources/AppwriteEnums/ProvinceType.swift', 'case capital'],
        'apple' => ['Sources/AppwriteEnums/WebhookEvent.swift', 'public static let userCreated', 'Sources/AppwriteEnums/LocalizedStatus.swift', 'case value1', 'Sources/AppwriteEnums/ProvinceType.swift', 'case capital'],
        'dotnet' => ['Appwrite/Enums/WebhookEvent.cs', 'public const string UserCreated', 'Appwrite/Enums/LocalizedStatus.cs', 'public static LocalizedStatus Value1', 'Appwrite/Enums/ProvinceType.cs', 'public static ProvinceType Capital'],
        'unity' => ['Runtime/Core/Enums/WebhookEvent.cs', 'public const string UserCreated', 'Runtime/Core/Enums/LocalizedStatus.cs', 'public static LocalizedStatus Value1', 'Runtime/Core/Enums/ProvinceType.cs', 'public static ProvinceType Capital'],
        'rust' => ['src/enums/webhook_event.rs', 'pub const UserCreated', 'src/enums/localized_status.rs', 'Value1,', 'src/enums/province_type.rs', 'Capital,'],
    ];

    /**
     * Replaces every `description` and `summary` in the fixture: prose that
     * tries to close the doc comment it is printed in and continue as code.
     */
    private const string PROSE = "zzprose */ zzbreak(); /* zzprose \"\"\" zzbreak() \\\nzzbreak(); zzprose\rzzbreak(); zzprose\u{85}zzbreak(); zzprose\u{2028}zzbreak(); zzprose";

    /**
     * Generated source extensions whose doc comments are C-style blocks, where
     * prose must never close the block or open a nested one.
     */
    private const array BLOCK_COMMENT_SOURCES = ['ts', 'js', 'mjs', 'php', 'kt', 'kts', 'java'];

    /**
     * Generated source extensions whose doc comments are line comments, where
     * every line of prose must still start with the comment marker.
     */
    private const array LINE_COMMENT_SOURCES = ['dart', 'swift', 'cs', 'rs', 'go', 'rb'];

    /**
     * Targets that ship Agent Skills, whose `SKILL.md` frontmatter name must
     * match the directory holding it.
     *
     * @var array<string, class-string<Language>>
     */
    private const array SKILL_LANGUAGES = [
        'skills' => Skills::class,
        'claude-plugin' => ClaudePlugin::class,
        'cursor-plugin' => CursorPlugin::class,
        'codex-plugin' => CodexPlugin::class,
    ];

    /**
     * Package metadata each language must declare, by generated path: the
     * runtime floor its code and dependencies need, and every interpreter
     * version the e2e suite runs it on.
     *
     * @var array<string, array<string, list<string>>>
     */
    private const array PACKAGE_METADATA = [
        'ruby' => [
            'appwrite.gemspec' => [
                "spec.required_ruby_version = '>= 2.7'",
                "spec.add_dependency 'mime-types', '~> 3.6'",
            ],
        ],
        'node' => [
            'package.json' => ['"engines": {', '"node": ">=20.0.0"'],
            'package-lock.json' => ['"engines": {', '"node": ">=20.0.0"'],
        ],
        'python' => [
            'setup.py' => [
                "python_requires='>=3.10'",
                "'requests>=2.34.2,<3'",
                "'urllib3>=2.8.0,<3'",
                "'Programming Language :: Python :: 3.10'",
                "'Programming Language :: Python :: 3.11'",
                "'Programming Language :: Python :: 3.12'",
                "'Programming Language :: Python :: 3.13'",
            ],
            'requirements.txt' => [
                'requests>=2.34.2,<3',
                'urllib3>=2.8.0,<3',
            ],
            'pyproject.toml' => [
                'requires-python = ">=3.10"',
                '"requests>=2.34.2,<3"',
                '"urllib3>=2.8.0,<3"',
                '"Programming Language :: Python :: 3.10"',
                '"Programming Language :: Python :: 3.11"',
                '"Programming Language :: Python :: 3.12"',
                '"Programming Language :: Python :: 3.13"',
            ],
        ],
    ];

    /** @var array<string, array<string, string>> */
    private const array PEER_DEPENDENCIES = [
        'react-native' => [
            'expo' => '>=52.0.0',
            'expo-file-system' => '>=18.0.0',
        ],
    ];

    /** @var array<string, list<string>> */
    private const array CDN_ARTIFACTS = [
        '.github/workflows/publish.yml' => ['npm run build:cdn', 'environment: cdn', 'r2:appwrite-sdk/zzcdn/', '--immutable'],
        'package.json' => ['"build:cdn"', '"!dist/cdn"'],
        'README.md' => ['https://sdk.appwrite.io/zzcdn/'],
        'rollup.config.mjs' => ['dist/cdn', "entries('src/enums', 'enums/')"],
    ];

    /** @var list<string> */
    private const array CDN_TOKENS = ['sdk.appwrite.io', 'r2:appwrite-sdk', 'build:cdn', 'dist/cdn', 'environment: cdn'];

    /** @var array<string, array<string, string>> generated tree per language and platform */
    private static array $generated = [];

    /** @return iterable<string, array{string}> */
    public static function languages(): iterable
    {
        foreach (\array_keys(self::languageClasses()) as $name) {
            yield $name => [$name];
        }
    }

    /** @return array<string, class-string<Language>> */
    private static function languageClasses(): array
    {
        return [
            'php' => PHP::class,
            'web' => Web::class,
            'node' => Node::class,
            'cli' => CLI::class,
            'ruby' => Ruby::class,
            'python' => Python::class,
            'dart' => Dart::class,
            'flutter' => Flutter::class,
            'react-native' => ReactNative::class,
            'go' => Go::class,
            'swift' => Swift::class,
            'apple' => Apple::class,
            'dotnet' => DotNet::class,
            'android' => Android::class,
            'kotlin' => Kotlin::class,
            'unity' => Unity::class,
            'rust' => Rust::class,
            'rest' => REST::class,
            'graphql' => GraphQL::class,
        ];
    }

    private function language(string $name): Language
    {
        $language = new ((self::languageClasses() + self::SKILL_LANGUAGES)[$name])();
        if ($language instanceof CLI) {
            $language->setExecutableName('appwrite');
        }

        return $language;
    }

    /**
     * The generated tree for a language and platform, generated once per run,
     * as lowercase path => lowercase contents.
     *
     * @return array<string, string>
     */
    private function generate(string $name, string $platform, string $unionVariant = '', ?Language $language = null, string $variant = ''): array
    {
        $key = $platform . $unionVariant . $variant;
        if (isset(self::$generated[$name][$key])) {
            return self::$generated[$name][$key];
        }

        $dir = self::OUTPUT . '/' . $name . '/' . $key;
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        if ($variant === '-optional-examples') {
            $properties = $document->components->schemas->player->properties;
            $properties->zznestedmissing = (object) ['type' => 'string'];
            $properties->zznestedempty = (object) ['type' => 'string', 'example' => ''];
            $properties->zznestedzero = (object) ['type' => 'string', 'example' => '0'];
            $properties->zznestedrequired = (object) ['type' => 'string'];
            $document->components->schemas->player->required[] = 'zznestedrequired';
            $body = $document->paths->{'/mock/tests/general/zzexamples'}->post->requestBody->content->{'application/json'}->schema;
            $body->required = [];
            $body->properties = (object) ['zzmissing' => (object) ['type' => 'string']];
        }
        if ($unionVariant !== '') {
            $union = $document->paths->{'/mock/tests/compound'}->get->responses->{'200'}->content->{'application/json'}->schema;
            foreach ($union->anyOf as $branch) {
                $branch->allOf[1]->required = ['type'];
                $branch->allOf[1]->properties = (object) [
                    'type' => (object) ['enum' => [$unionVariant === '-ambiguous' ? 'mock' : true]],
                ];
            }
        }
        $sdk = new SDK($language ?? $this->language($name), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform($platform)
            ->setNamespace(\in_array($name, ['android', 'kotlin'], true) ? 'io.appwrite' : 'appwrite')
            ->setExclude([
                'services' => [
                    ['name' => 'zzexcludedservice'],
                ],
                'methods' => [
                    ['name' => 'createExcludedGeneralFixture'],
                ],
            ])
            ->setTest('true')
            ->generate($dir);

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[\substr((string) $file->getPathname(), \strlen($dir) + 1)] = \strtolower((string) \file_get_contents($file->getPathname()));
            }
        }

        return self::$generated[$name][$key] = $files;
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAmbiguousConditionalResponsesFailGeneration(): void
    {
        $this->generate('php', 'server');
        $this->expectException(Exception::class);
        $this->generate('php', 'server', '-ambiguous');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testUnsupportedTypedConditionsFailRatherThanStringify(): void
    {
        $this->generate('php', 'server');
        $this->expectException(Exception::class);
        $this->generate('php', 'server', '-boolean');
    }

    /**
     * Prose from the spec is printed into doc comments. Whatever it contains,
     * the generated source keeps it inside the comment.
     */
    #[DataProvider('languages')]
    public function testProseStaysInsideDocComments(string $name): void
    {
        foreach (['client', 'server'] as $platform) {
            $files = $this->generateWithProse($name, $platform);
            $this->assertNotEmpty($files, "{$name} {$platform}: nothing generated");
            foreach ($files as $path => $contents) {
                $extension = \pathinfo((string) $path, PATHINFO_EXTENSION);
                if (\in_array($extension, self::BLOCK_COMMENT_SOURCES, true)) {
                    $this->assertStringNotContainsString('*/ zzbreak', $contents, "{$name} {$platform}: prose closes a comment in {$path}");
                    $this->assertStringNotContainsString('/* zzprose', $contents, "{$name} {$platform}: prose opens a comment in {$path}");
                }
                if ($extension === 'py') {
                    // Docstrings are string literals: `"""` ends one, and a
                    // backslash escapes whatever follows it.
                    $this->assertStringNotContainsString('""" zzbreak', $contents, "{$name} {$platform}: prose closes a docstring in {$path}");
                    $this->assertStringNotContainsString("zzbreak() \\\n", $contents, "{$name} {$platform}: prose escapes a docstring line break in {$path}");
                }
                if (\in_array($extension, self::LINE_COMMENT_SOURCES, true)) {
                    // C# also ends a line at U+0085, U+2028 and U+2029.
                    $breaks = $extension === 'cs' ? '/\r\n|\r|\n|\x{85}|\x{2028}|\x{2029}/u' : '/\r\n|\r|\n/';
                    $lines = (array) \preg_split($breaks, $contents);
                    $code = \array_filter($lines, static fn(string $line): bool => \str_starts_with(\ltrim($line), 'zzbreak'));
                    $this->assertSame([], $code, "{$name} {$platform}: prose continues as code in {$path}");
                }
            }
        }
    }

    /**
     * The generated tree for the fixture with every description and summary
     * replaced by PROSE.
     *
     * @return array<string, string>
     */
    private function generateWithProse(string $name, string $platform): array
    {
        $dir = self::OUTPUT . '/' . $name . '/prose-' . $platform;
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR);
        \array_walk_recursive($document, function (mixed &$value, int|string $key): void {
            if (($key === 'description' || $key === 'summary') && \is_string($value)) {
                $value = self::PROSE;
            }
        });

        $sdk = new SDK($this->language($name), Parser::parse($document));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform($platform)
            ->setNamespace(\in_array($name, ['android', 'kotlin'], true) ? 'io.appwrite' : 'appwrite')
            ->setTest('true')
            ->generate($dir);

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[\substr((string) $file->getPathname(), \strlen($dir) + 1)] = (string) \file_get_contents($file->getPathname());
            }
        }

        return $files;
    }

    /**
     * Output paths interpolate spec names and generator settings, and some
     * filters keep `.` and `/`. A package name made of parent directories
     * fails generation instead of writing beside the SDK.
     */
    public function testDestinationsStayInsideTheSdk(): void
    {
        $language = new Dart();
        $language->setPackageName('../../../zzescape');
        $escaped = self::OUTPUT . '/zzescape.dart';
        @\unlink($escaped);

        try {
            $this->generate('dart', 'server', language: $language, variant: '-escape');
            $this->fail('A package named ../../../zzescape generated an SDK');
        } catch (Exception $exception) {
            $this->assertStringContainsString('outside the SDK directory', $exception->getMessage());
        }

        $this->assertFileDoesNotExist($escaped);
    }

    /**
     * Templates print spec names as identifiers and spec values between
     * quotes without escaping them, so a name or value that could end the
     * identifier or the string is refused before anything is generated.
     */
    #[DataProvider('unsafeSpecs')]
    public function testUnsafeNamesAndValuesAreRefused(Closure $mutate): void
    {
        $document = $mutate(\json_decode((string) \file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('is not safe to generate');
        new SDK($this->language('kotlin'), Parser::parse($document));
    }

    /** @return iterable<string, array{Closure(array): array}> */
    public static function unsafeSpecs(): iterable
    {
        $property = static function (array $document, string $name, array $schema = ['type' => 'string']): array {
            $model = \array_key_first($document['components']['schemas']);
            $document['components']['schemas'][$model]['properties'][$name] = $schema;

            return $document;
        };
        $operation = static function (array $document, callable $change): array {
            $path = \array_key_first($document['paths']);
            $method = \array_key_first($document['paths'][$path]);
            $document['paths'][$path][$method] = $change($document['paths'][$path][$method]);

            return $document;
        };

        yield 'title' => [static fn(array $document): array => ['info' => ['title' => 'App"write'] + $document['info']] + $document];
        yield 'server URL with a quote' => [static fn(array $document): array => ['servers' => [['url' => 'https://example.com/v1"']]] + $document];
        yield 'server URL with interpolation' => [static fn(array $document): array => ['servers' => [['url' => 'https://example.com/${x}']]] + $document];
        yield 'tag' => [static fn(array $document): array => ['tags' => [['name' => '../zz']]] + $document];
        yield 'header name' => [static function (array $document): array {
            $document['components']['securitySchemes']['Project']['name'] = 'X-Appwrite-"Project';

            return $document;
        }];
        yield 'schema name' => [static function (array $document): array {
            $document['components']['schemas']['zz"model'] = ['type' => 'object', 'properties' => []];

            return $document;
        }];
        yield 'property name' => [static fn(array $document): array => $property($document, 'zz"; evil(); "')];
        yield 'properties that generate the same identifier' => [static fn(array $document): array => $property($property($document, 'zz-dup'), 'zzDup')];
        yield 'enum value with a quote' => [static fn(array $document): array => $property($document, 'zzenum', ['type' => 'string', 'enum' => ["a'b"]])];
        yield 'enum value with interpolation' => [static fn(array $document): array => $property($document, 'zzenum', ['type' => 'string', 'enum' => ['a${b}']])];
        yield 'enum value with a backslash' => [static fn(array $document): array => $property($document, 'zzenum', ['type' => 'string', 'enum' => ['a\(b)']])];
        yield 'enum value with a line break' => [static fn(array $document): array => $property($document, 'zzenum', ['type' => 'string', 'enum' => ["a\u{2028}b"]])];
        yield 'path' => [static function (array $document): array {
            $document['paths']["/zz'" . 'path'] = \reset($document['paths']);

            return $document;
        }];
        yield 'operation ID' => [static fn(array $document): array => $operation($document, static fn(array $method): array => ['operationId' => 'zz get'] + $method)];
        yield 'parameter name' => [static fn(array $document): array => $operation($document, static fn(array $method): array => ['parameters' => [
            ['name' => 'zz\\x', 'in' => 'query', 'schema' => ['type' => 'string']],
        ]] + $method)];
        yield 'header parameter name with a dollar sign' => [static fn(array $document): array => $operation($document, static fn(array $method): array => ['parameters' => [
            ['name' => '$zzheader', 'in' => 'header', 'schema' => ['type' => 'string']],
        ]] + $method)];
        yield 'media type' => [static fn(array $document): array => $operation($document, static fn(array $method): array => ['responses' => ['200' => [
            'description' => 'ok',
            'content' => ['application/json"; evil(); "' => ['schema' => ['type' => 'string']]],
        ]]] + $method)];
        yield 'enum title' => [static fn(array $document): array => $property($document, 'zzenum', ['type' => 'string', 'title' => "Zz]\nsystem('id')\n#", 'enum' => ['a']])];
    }

    /**
     * The production spec titles enums with punctuation, such as `teams.read`
     * and `Côte d'Ivoire`, and media types may carry parameters; neither is
     * refused.
     */
    public function testPunctuatedNamesAreAccepted(): void
    {
        $document = \json_decode((string) \file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR);
        $model = \array_key_first($document['components']['schemas']);
        $document['components']['schemas'][$model]['properties']['zzscope'] = ['type' => 'string', 'title' => 'teams.read', 'enum' => ['a']];
        $document['components']['schemas'][$model]['properties']['zzcountry'] = ['type' => 'string', 'title' => "Côte d'Ivoire", 'enum' => ['b']];
        $path = \array_key_first($document['paths']);
        $method = \array_key_first($document['paths'][$path]);
        $document['paths'][$path][$method]['responses']['200']['content']['application/json; charset=utf-8'] = ['schema' => ['type' => 'string']];
        $document['paths'][$path][$method]['responses']['200']['content']['application/vnd.zz+json; profile=zz!zz'] = ['schema' => ['type' => 'string']];
        $document['paths']['/zzcolon/{zzid}:activate'] = $document['paths'][$path];

        $this->assertInstanceOf(SDK::class, new SDK($this->language('web'), Parser::parse($document)));
    }

    /**
     * A parameter name may start with `$`. Kotlin and Dart interpolate it
     * inside a string literal, so the request key is escaped rather than read
     * as a reference to the argument of the same name; Ruby cannot write it as
     * a bare hash label, so the key is quoted.
     */
    public function testParameterKeysKeepTheirDollarSign(): void
    {
        $document = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'zzdollar']],
            'paths' => ['/zzdollar' => [
                'get' => [
                    'operationId' => 'zzdollarGet',
                    'tags' => ['zzdollar'],
                    'summary' => 'Get',
                    'description' => 'Get.',
                    'parameters' => [['name' => '$zzfield', 'in' => 'query', 'required' => true, 'description' => 'Field.', 'schema' => ['type' => 'string']]],
                    'responses' => ['204' => ['description' => 'ok']],
                ],
                'post' => [
                    'operationId' => 'zzdollarUpload',
                    'tags' => ['zzdollar'],
                    'summary' => 'Upload',
                    'description' => 'Upload.',
                    'requestBody' => ['required' => true, 'content' => ['multipart/form-data' => ['schema' => [
                        'type' => 'object',
                        'required' => ['$zzupload'],
                        'properties' => ['$zzupload' => ['type' => 'string', 'format' => 'binary', 'description' => 'File.']],
                    ]]]],
                    'responses' => ['204' => ['description' => 'ok']],
                ],
            ]],
        ];

        foreach (['kotlin' => 'src/main/kotlin/appwrite/services/Zzdollar.kt', 'android' => 'library/src/main/java/appwrite/services/Zzdollar.kt'] as $name => $file) {
            $service = $this->generateDocument($document, $name, 'dollar')[$file] ?? '';

            $this->assertStringContainsString('"\\$zzfield" to ', $service, $name);
            $this->assertStringContainsString('val paramName = "\\$zzupload"', $service, $name);
        }

        foreach (['dart', 'flutter'] as $name) {
            $service = $this->generateDocument($document, $name, 'dollar')['lib/services/zzdollar.dart'] ?? '';

            $this->assertStringContainsString('\'\\$zzfield\': zzfield,', $service, $name);
            $this->assertStringContainsString('final paramName = \'\\$zzupload\';', $service, $name);
        }

        $service = $this->generateDocument($document, 'ruby', 'dollar')['lib/test/services/zzdollar.rb'] ?? '';

        $this->assertStringContainsString('\'$zzfield\': zzfield,', $service);
    }

    public function testIdentifiersStayValidForHyphenatedFieldsAndReservedEnumKeys(): void
    {
        $document = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'zzident']],
            'paths' => ['/zzident' => ['get' => [
                'operationId' => 'zzidentGet',
                'tags' => ['zzident'],
                'summary' => 'Get',
                'description' => 'Get.',
                'parameters' => [['name' => 'zzresource', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string', 'enum' => ['index', 'xIndex', 'value']]]],
                'responses' => ['200' => ['description' => 'ok', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/zzreport']]]]],
            ]]],
            'components' => ['schemas' => ['zzreport' => [
                'type' => 'object',
                'required' => ['zz-field'],
                'properties' => ['zz-field' => ['type' => 'integer']],
            ]]],
        ];

        $kotlin = $this->generateDocument($document, 'kotlin', 'ident')['src/main/kotlin/appwrite/models/Zzreport.kt'] ?? '';
        $this->assertStringContainsString('val zzField: Long', $kotlin);

        $swift = $this->generateDocument($document, 'swift', 'ident')['Sources/TestModels/Zzreport.swift'] ?? '';
        $this->assertStringContainsString('case zzField = "zz-field"', $swift);

        $dart = $this->generateDocument($document, 'dart', 'ident')['lib/src/enums/zzresource.dart'] ?? '';
        $this->assertStringContainsString('xIndex2(value: "index")', $dart);
        $this->assertStringContainsString('xValue(value: "value")', $dart);

        $web = $this->generateDocument($document, 'web', 'ident')['src/models.ts'] ?? '';
        $this->assertStringContainsString("'zz-field': number;", $web);
    }

    private function removeDirectory(string $dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            $file->isDir() ? \rmdir($file->getPathname()) : \unlink($file->getPathname());
        }
        \rmdir($dir);
    }

    /** @return list<string> paths whose name or contents carry the token */
    private function filesContaining(array $files, string $token): array
    {
        $matches = [];
        foreach ($files as $path => $contents) {
            if (\str_contains(\strtolower((string) $path), $token) || \str_contains($contents, $token)) {
                $matches[] = $path;
            }
        }

        return $matches;
    }

    /**
     * Excluded fixtures never leak, and platform-bound fixtures appear on their
     * platforms alone, except where the target renders no such token at all.
     */
    #[DataProvider('languages')]
    public function testFixtureSelection(string $name): void
    {
        $trees = [];
        foreach (self::PLATFORMS as $platform) {
            $trees[$platform] = $this->generate($name, $platform);
        }

        foreach (self::TOKENS as $token => $platforms) {
            $found = [];
            foreach ($trees as $platform => $files) {
                $paths = $this->filesContaining($files, $token);
                if ($paths !== []) {
                    $found[$platform] = $paths;
                }
            }

            foreach (\array_diff(\array_keys($found), $platforms) as $platform) {
                $this->fail("{$name}/{$platform} leaks `{$token}`: " . \implode(', ', $found[$platform]));
            }

            if (\in_array($token, self::UNRENDERED[$name] ?? [], true)) {
                $this->assertSame([], $found, "{$name} now renders `{$token}`; drop it from UNRENDERED");
                continue;
            }

            $this->assertSame($platforms, \array_keys($found), "{$name} lacks `{$token}` on: " . \implode(', ', \array_diff($platforms, \array_keys($found))));
        }
    }

    /**
     * Unity projects save the selected services as the flags' integer value, so
     * each released service must keep its bit when a service is added.
     */
    public function testUnityServiceFlagsKeepTheirReleasedBits(): void
    {
        $files = $this->generate('unity', 'client');
        $paths = \array_values(\array_filter(\array_keys($files), fn(string $path): bool => \str_ends_with($path, 'AppwriteConfig.cs')));
        $this->assertCount(1, $paths);
        $config = $files[$paths[0]];

        // Bits as released in sdk-for-unity 0.9.0, plus the services 0.10.0 adds.
        $released = [
            'account' => 0, 'avatars' => 1, 'databases' => 2, 'documentsdb' => 3, 'functions' => 4,
            'graphql' => 5, 'locale' => 6, 'messaging' => 7, 'organization' => 8, 'presences' => 9,
            'storage' => 10, 'tablesdb' => 11, 'teams' => 12, 'vectorsdb' => 13, 'analytics' => 14,
            'apps' => 15, 'oauth2' => 16,
        ];
        \preg_match_all('/(\\w+) = 1 << (\\d+),/', $config, $flags, PREG_SET_ORDER);
        $this->assertNotEmpty($flags);
        foreach ($flags as [, $service, $bit]) {
            if (isset($released[$service])) {
                $this->assertSame($released[$service], (int) $bit, "unity moved the {$service} flag off its released bit");
            } else {
                $this->assertGreaterThanOrEqual(\count($released), (int) $bit, "unity gave the new {$service} flag a released bit");
            }
        }

        \preg_match_all('/ = 1 << (\\d+),/', $config, $bits);
        $this->assertSame(\array_unique($bits[1]), $bits[1], 'unity gave two services the same flag bit');
    }

    /**
     * A config saved by 0.9.0 has no record of the services it knew, so it is
     * read as knowing the services that had a flag in 0.9.0 (bits 0 to 14).
     */
    public function testUnityConfigReadsUntrackedAssetsAsKnowingTheReleasedFlags(): void
    {
        $files = $this->generate('unity', 'client');
        $paths = \array_values(\array_filter(\array_keys($files), fn(string $path): bool => \str_ends_with($path, 'AppwriteConfig.cs')));
        $config = $files[$paths[0]];

        $mask = 0;
        \preg_match_all('/ = 1 << (\\d+),/', $config, $bits);
        foreach ($bits[1] as $bit) {
            if ((int) $bit < 15) {
                $mask |= 1 << (int) $bit;
            }
        }
        $this->assertStringContainsString("var known = knownservices == 0 ? {$mask} : knownservices;", $config);
    }

    /**
     * A package installed from a git URL is read-only, so a manifest shipped in
     * it keeps a placeholder OAuth scheme that the app can never fill in. The
     * scheme the runtime waits for is registered at build time instead.
     */
    public function testUnityRegistersTheOAuthCallbackWithoutAPackageManifest(): void
    {
        $files = $this->generate('unity', 'client');

        $manifests = \array_filter(\array_keys($files), fn(string $path): bool => \str_contains(\strtolower($path), 'androidmanifest.xml'));
        $this->assertSame([], \array_values($manifests));

        foreach ($files as $path => $contents) {
            if (!\str_starts_with($path, 'docs~/')) {
                $this->assertStringNotContainsString('<your_project_id>', $contents, "{$path} ships a project ID placeholder");
            }
        }

        $this->assertStringContainsString('$"appwrite-callback-{projectid}"', $files['Runtime/Core/WebAuthComponent.cs']);
        $processor = $files['Editor/AppwriteAndroidManifestProcessor.cs'] ?? '';
        $this->assertStringContainsString(': ipostgenerategradleandroidproject', $processor);
        $this->assertStringContainsString('callbackschemeprefix = "appwrite-callback-"', $processor);
        $this->assertStringContainsString('"android.intent.category.browsable"', $processor);
    }

    /**
     * The setup window links to this SDK's documentation and the configured
     * community, not the generator's homepage URL or a fixed invite.
     */
    public function testUnitySetupWindowLinksToTheSdkDocumentation(): void
    {
        $window = $this->generate('unity', 'client')['Editor/AppwriteSetupWindow.cs'];

        $this->assertStringContainsString('application.openurl("https://github.com/', $window);
        $this->assertStringContainsString('#readme")', $window);
        $this->assertStringNotContainsString('discord.gg', $window);
    }

    /**
     * A plugin left out of the WebGL player is missing when that player compiles,
     * so its namespace may only be imported where WebGL players skip the code.
     */
    public function testUnityImportsPluginsLeftOutOfWebglOnlyOffWebgl(): void
    {
        $files = $this->generate('unity', 'client');

        $excluded = [];
        foreach ($files as $path => $contents) {
            if (\str_ends_with($path, '.dll.meta') && !\str_contains($contents, "webgl: webgl\n    second:\n      enabled: 1")) {
                $excluded[] = \strtolower(\basename($path, '.dll.meta'));
            }
        }
        $this->assertContains('mqttnet', $excluded);

        // Conditions under which a WebGL player does not compile the code.
        $offWebgl = ['!unity_webgl || unity_editor', '!(unity_webgl && !unity_editor)', 'else unity_webgl && !unity_editor'];
        foreach ($files as $path => $contents) {
            if (!\str_ends_with($path, '.cs')) {
                continue;
            }
            $conditions = [];
            foreach (\explode("\n", $contents) as $line) {
                $line = \trim($line);
                if (\str_starts_with($line, '#if ')) {
                    $conditions[] = \substr($line, 4);
                } elseif ($line === '#else') {
                    $conditions[] = 'else ' . \array_pop($conditions);
                } elseif ($line === '#endif') {
                    \array_pop($conditions);
                } elseif (\preg_match('/^using ([\w.]+);$/', $line, $using) && \array_intersect($conditions, $offWebgl) === []) {
                    foreach ($excluded as $plugin) {
                        $this->assertFalse(
                            $using[1] === $plugin || \str_starts_with($using[1], $plugin . '.'),
                            "{$path} imports {$using[1]} on WebGL, where {$plugin}.dll is not included",
                        );
                    }
                }
            }
        }
    }

    /**
     * The analytics tracking helpers are hand-written companions to a generated
     * service. Shipping them into an SDK whose spec has no analytics service
     * leaves files, and entry-point exports, naming a class that is never
     * generated — which is what broke appwrite/sdk-for-flutter#336.
     */
    public function testAnalyticsCompanionsFollowTheService(): void
    {
        $companions = [
            'flutter' => ['lib/src/tracking.dart', 'lib/src/tracking_observer.dart'],
            'web' => ['src/services/analytics-tracking.ts', 'src/tracker.ts'],
        ];
        $entrypoints = [
            'flutter' => ['lib/packageName.dart', 'src/tracking'],
            'web' => ['src/index.ts', 'analytics-tracking'],
        ];

        foreach ($companions as $name => $paths) {
            // The shared fixture carries no analytics service.
            $without = $this->generate($name, 'client');
            foreach ($paths as $path) {
                $this->assertArrayNotHasKey($path, $without, "{$name} shipped {$path} without the analytics service");
            }
            [$entry, $token] = $entrypoints[$name];
            $this->assertArrayHasKey($entry, $without, "{$name} did not generate {$entry}");
            $this->assertStringNotContainsString($token, $without[$entry], "{$name} {$entry} exports a companion it did not generate");

            $with = $this->generateWithAnalytics($name);
            foreach ($paths as $path) {
                $this->assertArrayHasKey($path, $with, "{$name} did not ship {$path} alongside the analytics service");
            }
            $this->assertStringContainsString($token, $with[$entry], "{$name} {$entry} does not export the companion");
        }
    }

    /**
     * @return array<string, string>
     */
    private function generateWithAnalytics(string $name): array
    {
        $dir = self::OUTPUT . '/' . $name . '/analytics';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $operation = clone $document->paths->{'/ping'}->get;
        $operation->tags = ['analytics'];
        $operation->operationId = 'analyticsCreateEvent';
        $document->paths->{'/analytics/event'} = (object) ['get' => $operation];
        $document->tags[] = (object) ['name' => 'analytics', 'description' => 'Analytics'];

        $sdk = new SDK($this->language($name), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('client')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[\substr((string) $file->getPathname(), \strlen($dir) + 1)] = \strtolower((string) \file_get_contents($file->getPathname()));
            }
        }

        return $files;
    }

    public function testJavascriptClientOwnership(): void
    {
        $targets = [
            ['web', false, 'client.flatten(apipayload)', 'export class push {'],
            ['node', false, null, null],
            ['react-native', true, 'service.flatten(apipayload)', 'export class push extends service {'],
        ];

        foreach ($targets as [$name, $hasService, $flatten, $pushDeclaration]) {
            foreach (self::PLATFORMS as $platform) {
                $files = $this->generate($name, $platform);
                $this->assertSame($hasService, isset($files['src/service.ts']), "{$name}/{$platform} base module");
                if ($pushDeclaration !== null) {
                    $this->assertStringContainsString($pushDeclaration, $files['src/services/push.ts']);
                }
                if ($flatten !== null && $platform === 'client') {
                    $this->assertStringContainsString($flatten, $files['src/services/general.ts']);
                }
            }
        }
    }

    /**
     * The Flutter plugin and the React Native module embed the Android Push code.
     * An app can depend on more than one of the three SDKs, so no Android class
     * may share a fully qualified name with another SDK's, or the build fails on
     * duplicate classes.
     */
    public function testEmbeddedAndroidPushClassesDoNotCollide(): void
    {
        $classes = [];
        foreach (['android', 'flutter', 'react-native'] as $name) {
            $classes[$name] = [];
            foreach ($this->generate($name, 'client') as $path => $contents) {
                if (!\str_ends_with($path, '.kt') || !\preg_match('/^package ([a-z0-9_.]+)/m', $contents, $package)) {
                    continue;
                }
                \preg_match_all('/^(?:[a-z]+ )*(?:class|object|interface) ([a-z0-9_]+)/m', $contents, $declarations);
                foreach ($declarations[1] as $class) {
                    $classes[$name][] = $package[1] . '.' . $class;
                }
            }
        }

        $this->assertSame([], \array_values(\array_intersect($classes['android'], $classes['flutter'])));
        $this->assertSame([], \array_values(\array_intersect($classes['android'], $classes['react-native'])));
        $this->assertSame([], \array_values(\array_intersect($classes['flutter'], $classes['react-native'])));
        $this->assertContains('io.appwrite.flutter.services.pushservice', $classes['flutter']);
        $this->assertContains('io.appwrite.reactnative.services.pushservice', $classes['react-native']);
        $this->assertContains('io.appwrite.services.pushservice', $classes['android']);
    }

    /**
     * A `{}` spec default decodes to an empty PHP array, and a number param
     * without an example used to print `null`; both must keep their declared
     * type in the generated TypeScript.
     */
    #[RunInSeparateProcess]
    public function testJavascriptDefaultsAndExamplesKeepTheirTypes(): void
    {
        $defaults = [];
        $examples = [];
        foreach (['web', 'node', 'react-native'] as $name) {
            foreach (self::PLATFORMS as $platform) {
                $files = $this->generate($name, $platform);
                $general = $files['src/services/general.ts'] ?? '';
                if (\str_contains($general, 'getpreset(')) {
                    $this->assertStringContainsString('settings: {}', $general, "{$name}/{$platform} object default");
                    $this->assertStringNotContainsString('settings: []', $general, "{$name}/{$platform} object default");
                    $defaults[] = "{$name}/{$platform}";
                }
                if (isset($files['docs/examples/foo/get.md'])) {
                    $this->assertMatchesRegularExpression('/\\by: 0\\b/', $files['docs/examples/foo/get.md'], "{$name}/{$platform} number example");
                    $examples[] = "{$name}/{$platform}";
                }
            }
        }
        $this->assertNotEmpty($defaults);
        $this->assertNotEmpty($examples);
    }

    /**
     * Missing optional strings must stay unset; explicit examples, including
     * the string "0", numeric zero and false, must remain usable arguments.
     */
    public function testOptionalStringExamplesStayUnset(): void
    {
        $examples = [
            ['web', 'client', '', 'zzmissing: undefined', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['node', 'server', '', 'zzmissing: undefined', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['react-native', 'client', '', 'zzmissing: undefined', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['dart', 'server', '', 'zzmissing: null', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['flutter', 'client', '', 'zzmissing: null', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['php', 'server', '', 'zzmissing: null', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['python', 'server', '', 'zzmissing = none', "zzzero = '0'", 'zzcount = 0', 'zzenabled = false'],
            ['ruby', 'server', '', 'zzmissing: nil', "zzzero: '0'", 'zzcount: 0', 'zzenabled: false'],
            ['dotnet', 'server', '', 'zzmissing: null', 'zzzero: "0"', 'zzcount: 0', 'zzenabled: false'],
            ['unity', 'client', '', 'zzmissing: null', 'zzzero: "0"', 'zzcount: 0', 'zzenabled: false'],
            ['swift', 'server', '', 'zzmissing: nil', 'zzzero: "0"', 'zzcount: 0', 'zzenabled: false'],
            ['apple', 'client', '', 'zzmissing: nil', 'zzzero: "0"', 'zzcount: 0', 'zzenabled: false'],
            ['kotlin', 'server', 'kotlin/', 'zzmissing = null', 'zzzero = "0"', 'zzcount = 0', 'zzenabled = false'],
            ['android', 'client', 'kotlin/', 'zzmissing = null', 'zzzero = "0"', 'zzcount = 0', 'zzenabled = false'],
            ['kotlin', 'server', 'java/', 'null, // zzmissing', '"0", // zzzero', '0l, // zzcount', 'false, // zzenabled'],
            ['android', 'client', 'java/', 'null, // zzmissing', '"0", // zzzero', '0l, // zzcount', 'false, // zzenabled'],
            ['go', 'server', '', null, 'withzzexampleszzzero("0")', 'withzzexampleszzcount(0)', 'withzzexampleszzenabled(false)'],
            ['rust', 'server', '', 'none, // optional', 'some("0")', 'some(0)', 'some(false)'],
            ['rest', 'client', '', null, '"zzzero": "0"', '"zzcount": 0', '"zzenabled": false'],
            ['graphql', 'client', '', null, 'zzzero: "0"', 'zzcount: 0', 'zzenabled: false'],
        ];

        foreach ($examples as [$name, $platform, $directory, $missing, $zero, $count, $enabled]) {
            $files = $this->generate($name, $platform);
            $root = $name === 'unity' ? 'docs~' : 'docs';
            $path = "{$root}/examples/{$directory}general/zzexamples.md";
            $this->assertArrayHasKey($path, $files, $name);
            $example = $files[$path];
            foreach ([$zero, $count, $enabled, 'required-value', 'kept-value'] as $value) {
                $this->assertStringContainsString($value, $example, $name);
            }
            if ($missing !== null) {
                if ($name === 'rust') {
                    $this->assertSame(2, \substr_count($example, $missing));
                }
                $this->assertStringContainsString($missing, $example, $name);
                $this->assertStringContainsString(\str_replace('zzmissing', 'zzempty', $missing), $example, $name);
            } else {
                $this->assertStringNotContainsString('zzmissing', $example, $name);
                $this->assertStringNotContainsString('zzempty', $example, $name);
            }
            $this->assertDoesNotMatchRegularExpression('/(?:zzmissing|zzempty)[^\n]*[\'\"][\'\"]/', $example, $name);
        }

        $rest = $this->generate('rest', 'client')['docs/examples/general/zzmultipart.md'];
        $this->assertStringNotContainsString('name="zzmissing"', $rest);
        $this->assertStringNotContainsString('name="zzempty"', $rest);
        $this->assertStringContainsString('name="zzvalue"', $rest);

        $cli = $this->generate('cli', 'console')['docs/examples/general/zzexamples.md'];
        $this->assertStringContainsString('required-value', $cli);
        $this->assertStringNotContainsString('--zzmissing', $cli);
        $this->assertStringNotContainsString('--zzempty', $cli);
    }

    public function testOptionalExamplesPreserveRequiredBodiesAndNestedModels(): void
    {
        $python = $this->generate('python', 'server', variant: '-optional-examples');
        foreach (['create-player', 'create-players'] as $method) {
            $example = $python["docs/examples/general/{$method}.md"];
            $this->assertStringNotContainsString('zznestedmissing', $example);
            $this->assertStringNotContainsString('zznestedempty', $example);
            $this->assertStringContainsString("zznestedzero = '0'", $example);
            $this->assertStringContainsString("id = 'player123'", $example);
            $this->assertStringContainsString("zznestedrequired = ''", $example);
        }

        $rest = $this->generate('rest', 'client', variant: '-optional-examples')['docs/examples/general/zzexamples.md'];
        $this->assertStringNotContainsString('zzmissing', $rest);
        $this->assertMatchesRegularExpression('/\{\s*\}\s*```/', $rest);
    }

    /**
     * A canonical document keys `x-appwrite.auth` by platform. The fixture's
     * platform-auth operation lists `Project` for client and `Project, Key` for
     * server plus an optional Session, and the server alias variant lists
     * `Project, JWT`. Optional security must not become example configuration;
     * the Web E2E proves it still reaches a location method's query string.
     */
    public function testExampleCredentialsFollowPlatformAuth(): void
    {
        $examples = [
            ['server', 'docs/examples/general/zzderivedauth.md', ['->setproject(', '->setkey('], ['->setjwt(', '->setsession(']],
            ['client', 'docs/examples/general/zzderivedauth.md', ['->setproject('], ['->setkey(', '->setjwt(', '->setsession(']],
            ['server', 'docs/examples/general/zzplatformalias.md', ['->setproject(', '->setjwt('], ['->setkey(', '->setsession(']],
            ['client', 'docs/examples/general/zzplatformalias.md', ['->setproject('], ['->setkey(', '->setjwt(', '->setsession(']],
            ['client', 'docs/examples/general/download.md', ['->setproject('], ['->setimpersonateuserid(']],
        ];

        foreach ($examples as [$platform, $path, $present, $absent]) {
            $files = $this->generate('php', $platform);
            $this->assertArrayHasKey($path, $files, "php/{$platform} did not generate {$path}");
            foreach ($present as $call) {
                $this->assertStringContainsString($call, $files[$path], "php/{$platform} {$path} lacks {$call}");
            }
            foreach ($absent as $call) {
                $this->assertStringNotContainsString($call, $files[$path], "php/{$platform} {$path} configures {$call}");
            }
        }
    }

    public function testPhpExamplesParse(): void
    {
        $failures = [];
        $script = \tempnam(\sys_get_temp_dir(), 'example');
        foreach (self::PLATFORMS as $platform) {
            foreach ($this->generate('php', $platform) as $path => $contents) {
                if (!\str_starts_with($path, 'docs/examples/') || !\preg_match('/```php\n(.*?)```/s', $contents, $match)) {
                    continue;
                }
                \file_put_contents($script, $match[1]);
                \exec('php -n -l ' . \escapeshellarg($script) . ' 2>&1', $output, $code);
                if ($code !== 0) {
                    $failures[] = "php/{$platform} {$path}";
                }
                $output = [];
            }
        }
        \unlink($script);

        $this->assertSame([], $failures, 'Generated PHP examples must parse');
    }

    /**
     * A JSON example for an untyped object array must render as Swift
     * dictionary literals, not as the JavaScript-style `{ ... }` the spec
     * carries. Quotes inside example strings must stay escaped, and an empty
     * array example must stay an array rather than becoming a dictionary.
     */
    #[DataProvider('swiftLanguages')]
    public function testObjectArrayExamplesUseSwiftLiterals(string $name, string $platform): void
    {
        $files = $this->generate($name, $platform);

        $documents = 'docs/examples/general/create-documents.md';
        $this->assertArrayHasKey($documents, $files, "{$name}/{$platform} did not generate {$documents}");
        $this->assertStringContainsString('"$id": "one"', $files[$documents]);
        $this->assertStringContainsString('"title": "say \\"hello\\""', $files[$documents]);
        $this->assertStringNotContainsString('{', $files[$documents]);

        $oauth = 'docs/examples/general/oauth-2.md';
        $this->assertArrayHasKey($oauth, $files, "{$name}/{$platform} did not generate {$oauth}");
        $this->assertStringContainsString('scopes: []', $files[$oauth]);
    }

    /**
     * @return Iterator<string, array{string, string}>
     */
    public static function swiftLanguages(): Iterator
    {
        yield 'swift' => ['swift', 'server'];
        yield 'apple' => ['apple', 'client'];
    }

    /**
     * Kotlin and Java examples must compile as written: quotes inside example
     * strings stay escaped, Kotlin's `$` is not left to interpolate, and named
     * arguments use the parameter names the service method declares.
     */
    #[DataProvider('kotlinLanguages')]
    public function testExamplesUseKotlinAndJavaLiterals(string $name, string $platform): void
    {
        $files = $this->generate($name, $platform);

        $kotlin = 'docs/examples/kotlin/general/create-documents.md';
        $this->assertArrayHasKey($kotlin, $files, "{$name}/{$platform} did not generate {$kotlin}");
        $this->assertStringContainsString('"\\$id" to "one"', $files[$kotlin]);
        $this->assertStringContainsString('"title" to "say \\"hello\\""', $files[$kotlin]);

        $java = 'docs/examples/java/general/create-documents.md';
        $this->assertArrayHasKey($java, $files, "{$name}/{$platform} did not generate {$java}");
        $this->assertStringContainsString('"$id", "one"', $files[$java]);
        $this->assertStringContainsString('"title", "say \\"hello\\""', $files[$java]);

        $named = 'docs/examples/kotlin/general/snake-case-params.md';
        $this->assertArrayHasKey($named, $files, "{$name}/{$platform} did not generate {$named}");
        $this->assertStringContainsString('grantid = "<grant_id>"', $files[$named]);
        $this->assertStringNotContainsString('grant_id =', $files[$named]);
    }

    /**
     * @return Iterator<string, array{string, string}>
     */
    public static function kotlinLanguages(): Iterator
    {
        yield 'kotlin' => ['kotlin', 'server'];
        yield 'android' => ['android', 'client'];
    }

    public function testRustBodylessResponsesUseUnitWithJsonAccept(): void
    {
        $files = $this->generate('rust', 'server');
        $general = $files['src/services/general.rs'];

        foreach (['empty', 'redirect'] as $method) {
            $this->assertMatchesRegularExpression(
                '/pub async fn ' . $method . '\(&self\) -> crate::error::result<\(\)> \{[^}]*api_headers\.insert\("accept"\.to_string\(\), "application\/json"\.to_string\(\)\);/s',
                $general,
            );
        }

        $this->assertStringContainsString('"application/json, text/plain".to_string()', $general);
    }

    public function testRustParsesFloatsExactly(): void
    {
        $files = $this->generate('rust', 'server');

        $this->assertStringContainsString('serde_json = { version = "1.0.149", features = ["float_roundtrip"] }', $files['Cargo.toml']);
    }

    public function testRustQueryBuilderChainsEveryQuery(): void
    {
        $files = $this->generate('rust', 'server');
        $queries = $this->rustPublicFunctions(strstr($files['src/query/mod.rs'], 'impl query {'));
        $chained = $this->rustPublicFunctions($files['src/query/builder.rs']);

        $missing = array_values(array_diff($queries, $chained, ['builder', 'on', 'to_value']));

        $this->assertSame([], $missing, 'Query constructors the builder cannot chain: ' . implode(', ', $missing));
    }

    public function testEverySdkSkillShowsTheQueryFamilies(): void
    {
        $skills = array_filter(
            $this->generate('skills', 'console'),
            fn (string $path): bool => basename($path) === 'SKILL.md' && !str_contains($path, '-cli/') && !str_contains($path, 'deploy'),
            ARRAY_FILTER_USE_KEY,
        );
        $families = ['aggregation' => '/\bcount\(/', 'grouping' => '/group_?by\(/', 'having' => '/having\(/', 'joins' => '/join\(/', 'builder' => '/(?:query(?:\.|::)builder|newbuilder)\b/'];

        $this->assertNotEmpty($skills);
        foreach ($skills as $path => $skill) {
            $missing = array_keys(array_filter($families, fn (string $pattern): bool => preg_match($pattern, $skill) !== 1));
            $this->assertSame([], $missing, "{$path} has no query example for: " . implode(', ', $missing));
        }
    }

    public function testSdkSkillsInstallTheLatestSdk(): void
    {
        $skills = array_filter(
            $this->generate('skills', 'console'),
            fn (string $path): bool => basename($path) === 'SKILL.md',
            ARRAY_FILTER_USE_KEY,
        );

        $this->assertNotEmpty($skills);
        foreach ($skills as $path => $skill) {
            $this->assertDoesNotMatchRegularExpression('/\\bappwrite(?:\\s*=\\s*"|@|==)\\d/i', $skill, "{$path} pins an SDK version that goes stale on the next release");
        }
    }

    public function testCodexReadmeListsTheGeneratedSkillIds(): void
    {
        $files = $this->generate('codex-plugin', 'console');
        preg_match_all('#^- `([a-z0-9-]+)`$#m', $files['README.md'], $matches);
        $generated = array_map(
            fn (string $path): string => basename(dirname($path)),
            array_filter(array_keys($files), fn (string $path): bool => basename($path) === 'SKILL.md'),
        );

        $this->assertNotEmpty($matches[1]);
        $this->assertSame([], array_values(array_diff($matches[1], $generated)), 'README lists skills the plugin does not ship');
    }

    public function testRubyLoadsOnlyTheCgiEscapeLibrary(): void
    {
        $client = $this->generate('ruby', 'server')['lib/appwrite/client.rb'];

        $this->assertMatchesRegularExpression("/^require 'cgi\\/escape'$/m", $client);
        $this->assertDoesNotMatchRegularExpression("/^require 'cgi'$/m", $client, 'Ruby 4.0 removed the cgi library; only cgi/escape ships with it');
    }

    /**
     * File content must not be able to end its part and add form fields such
     * as permissions, so the boundary is random for every request and field
     * and file names cannot close their quotes or header line.
     */
    public function testRubyMultipartCannotBeForged(): void
    {
        $client = $this->generate('ruby', 'server')['lib/appwrite/client.rb'];

        $this->assertStringNotContainsString('a30#3ad1', $client);
        $this->assertStringContainsString('boundary = "----#{securerandom.hex(16)}"', $client);
        $this->assertStringContainsString('name=\\"#{escape_form_data_name(key)}\\"; filename=\\"#{escape_form_data_name(value.filename)}\\"', $client);
    }

    /**
     * A cookie's value is the session secret, and device logs are readable by
     * crash reporters and anyone with a debug connection.
     */
    public function testCookieValuesAreNotLogged(): void
    {
        $android = $this->generate('android', 'client')['library/src/main/java/io/appwrite/cookies/stores/SharedPreferencesCookieStore.kt'] ?? null;
        $unity = $this->generate('unity', 'client')['Runtime/Core/CookieContainer.cs'];

        $this->assertNotNull($android);
        $this->assertStringNotContainsString('value = $value', $android);
        $this->assertDoesNotMatchRegularExpression('/debug\.log\([^;]*\{(cookie|c)\}/', $unity);
    }

    /**
     * The installer checks the download against its checksum and later copies
     * it with sudo. Between the two, a file in a directory other users can
     * write can be replaced, so the download goes into a private directory.
     */
    public function testCliInstallerDownloadsIntoAPrivateDirectory(): void
    {
        $installer = $this->generate('cli', 'console')['install.sh'];

        $this->assertStringContainsString('appwrite_temp_dir=$(mktemp -d)', $installer);
        $this->assertStringContainsString('appwrite_temp_name="$appwrite_temp_dir/', $installer);
        $this->assertStringNotContainsString('temp-$(date', $installer);
    }

    public function testDotNetKeepsDateTimeStringsAsStrings(): void
    {
        $converter = $this->generate('dotnet', 'server')['Appwrite/Converters/ObjectToInferredTypesConverter.cs'];

        $this->assertMatchesRegularExpression('/case jsonvaluekind\.string:\s+return element\.getstring\(\);/', $converter);
        $this->assertStringNotContainsString('trygetdatetime', $converter, 'Inferring DateTime from strings rewrites server datetimes in the current culture');
    }

    #[DataProvider('swiftQueryPlatforms')]
    public function testSwiftQueriesTakeTheAttributeUnlabelled(string $name, string $platform): void
    {
        $files = $this->generate($name, $platform);

        foreach (['sources/appwrite/query.swift', 'sources/appwrite/query/builder.swift'] as $path) {
            $source = $files[$this->fileKey($files, $path)];
            preg_match_all('/(@available\([^\n]*deprecated[^\n]*\n\s*)?public (?:static )?func ([a-z]+)\((?!_ |\))[a-z]/i', $source, $matches, PREG_SET_ORDER);
            $labelled = array_map(fn (array $match): string => $match[2], array_filter($matches, fn (array $match): bool => $match[1] === '' && $match[2] !== 'encode'));

            $this->assertSame([], array_values($labelled), "{$name} {$path} queries that need a first-argument label: " . implode(', ', $labelled));
        }
    }

    public function testAppleRealtimeClosesOnceWhenTheConnectionDropsWithoutACloseFrame(): void
    {
        $files = $this->generate('apple', 'client');
        $handler = $files[$this->fileKey($files, 'sources/appwrite/websockets/messagehandler.swift')];

        $this->assertMatchesRegularExpression(
            '/func channelInactive\(context: ChannelHandlerContext\) \{\s*if !closeDelivered \{\s*closeDelivered = true\s*notifyClose\(channel: context\.channel, data: Data\(\)\)\s*\}\s*context\.fireChannelInactive\(\)/i',
            $handler,
            'a dropped connection without a close frame must reach onClose so Realtime reconnects'
        );
        $this->assertMatchesRegularExpression(
            '/case \.connectionClose:.*?closeDelivered = true.*?notifyClose\(/is',
            $handler,
            'a close frame must mark the close delivered so the following channelInactive does not reconnect again'
        );
    }

    public static function swiftQueryPlatforms(): Iterator
    {
        yield 'swift' => ['swift', 'server'];
        yield 'apple' => ['apple', 'client'];
    }

    /**
     * @param array<string, string> $files
     */
    private function fileKey(array $files, string $suffix): string
    {
        foreach (array_keys($files) as $key) {
            if (str_ends_with(strtolower($key), $suffix)) {
                return $key;
            }
        }

        $this->fail("no generated file ending in {$suffix}");
    }

    /**
     * @return array<string>
     */
    private function rustPublicFunctions(string $source): array
    {
        preg_match_all('/^    pub fn ([a-z_]+)\b/m', $source, $matches);

        return array_values(array_unique($matches[1]));
    }

    #[DataProvider('languages')]
    public function testEnumsAreDeclared(string $name): void
    {
        if (!isset(self::ENUM_DECLARATIONS[$name])) {
            $this->markTestSkipped("{$name} declares no enums.");
        }

        [$knownPath, $knownDeclaration, $localizedPath, $localizedDeclaration, $annotatedPath, $annotatedDeclaration] = self::ENUM_DECLARATIONS[$name];
        $files = $this->generate($name, 'server');

        foreach ([[$knownPath, $knownDeclaration], [$knownPath, 'user.updated'], [$localizedPath, $localizedDeclaration], [$annotatedPath, $annotatedDeclaration]] as [$path, $declaration]) {
            $this->assertArrayHasKey($path, $files, "{$name} did not generate {$path}");
            $this->assertStringContainsString(\strtolower($declaration), $files[$path], "{$name}: {$path} lacks `{$declaration}`");
        }
    }

    /** @return iterable<string, array{string}> */
    public static function packageLanguages(): iterable
    {
        foreach (\array_keys(self::PACKAGE_METADATA) as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('packageLanguages')]
    public function testPackageMetadataIsDeclared(string $name): void
    {
        $files = $this->generate($name, 'server');

        foreach (self::PACKAGE_METADATA[$name] as $path => $declarations) {
            $this->assertArrayHasKey($path, $files, "{$name} did not generate {$path}");
            foreach ($declarations as $declaration) {
                $this->assertStringContainsString(\strtolower($declaration), $files[$path], "{$name}: {$path} lacks `{$declaration}`");
            }
        }
    }

    /** @return iterable<string, array{string}> */
    public static function guideLanguages(): iterable
    {
        foreach (\array_keys(self::languageClasses()) as $name) {
            if (!\in_array($name, ['rest', 'graphql'], true)) {
                yield $name => [$name];
            }
        }
    }

    #[DataProvider('guideLanguages')]
    public function testReadmeRendersTheGuideVerbatim(string $name): void
    {
        $guide = "```\nclient.set('<REGION>', \"a & b\") => ok\n```";
        $dir = self::OUTPUT . '/' . $name . '/guide';
        $this->removeDirectory($dir);

        new SDK($this->language($name), Parser::parse((string) \file_get_contents(self::FIXTURE)))
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace(\in_array($name, ['android', 'kotlin'], true) ? 'io.appwrite' : 'appwrite')
            ->setGettingStarted($guide)
            ->setExamples($guide)
            ->setTest('true')
            ->generate($dir);

        $readme = (string) \file_get_contents($dir . '/README.md');
        $this->assertStringContainsString($guide, $readme, "{$name}: README.md escapes the getting started guide");
        if (\is_file($dir . '/example/README.md')) {
            $this->assertStringContainsString($guide, (string) \file_get_contents($dir . '/example/README.md'), "{$name}: example/README.md escapes the examples");
        }
    }

    /** @return iterable<string, array{string}> */
    public static function peerLanguages(): iterable
    {
        foreach (\array_keys(self::PEER_DEPENDENCIES) as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('peerLanguages')]
    public function testNativeModulesArePeerDependencies(string $name): void
    {
        $files = $this->generate($name, 'client');

        foreach (['package.json' => null, 'package-lock.json' => ''] as $path => $root) {
            $this->assertArrayHasKey($path, $files, "{$name} did not generate {$path}");
            $package = \json_decode($files[$path], true, flags: JSON_THROW_ON_ERROR);
            if ($root !== null) {
                $package = $package['packages'][$root];
            }

            foreach (self::PEER_DEPENDENCIES[$name] as $dependency => $range) {
                $this->assertSame($range, $package['peerdependencies'][$dependency] ?? null, "{$name}: {$path} does not declare {$dependency} as a {$range} peer");
                $this->assertArrayNotHasKey($dependency, $package['dependencies'] ?? [], "{$name}: {$path} bundles {$dependency} as a dependency");
            }
        }
    }

    public function testAppleUIKitExampleResolvesTheGeneratedSdkLocally(): void
    {
        $files = $this->generate('apple', 'client');
        $project = $files['example-uikit/UIKitExample.xcodeproj/project.pbxproj'];
        $this->assertSame(1, \preg_match('/(?<id>[a-f0-9]{24}) \/\*[^\n]*\*\/ = \{\s*isa = xclocalswiftpackagereference;\s*relativepath = (?<path>[^;]+);/', $project, $reference));
        $this->assertSame(1, \preg_match('/packagereferences = \((?<references>[^)]*)\);/', $project, $registered));
        $this->assertStringContainsString($reference['id'], $registered['references']);
        $relativePath = \trim($reference['path'], " \t\n\r\0\x0B\"");
        $this->assertSame(
            \realpath(self::OUTPUT . '/apple/client/Package.swift'),
            \realpath(self::OUTPUT . '/apple/client/example-uikit/' . $relativePath . '/Package.swift'),
        );
        $this->assertStringNotContainsString('../../../examples/apple', $project);
    }

    /**
     * Twig autoescapes to HTML, so a description that reaches a template through
     * an unsafe filter arrives as `&quot;` rather than `"`. Go doc comments are
     * read as plain text, so the entity is what the reader sees.
     */
    /**
     * A schema reachable only from a request body is generated as a request
     * model. Go names the two kinds apart, the only place a generated tree
     * shows which scope produced a model.
     */
    public function testRequestModelsComeFromBodyOnlySchemas(): void
    {
        foreach (\array_keys(self::languageClasses()) as $name) {
            if (!\in_array('requestModel', \array_column($this->language($name)->getFiles(), 'scope'), true)) {
                continue;
            }

            $this->assertNotEmpty(
                $this->filesContaining($this->generate($name, 'server'), 'player'),
                "{$name} does not generate the body-only fixture schema"
            );
        }

        $go = $this->generate('go', 'server');
        $this->assertArrayHasKey('models/player.go', $go, 'go no longer generates the body-only fixture schema as a model');
        $this->assertStringContainsString('request model', $go['models/player.go']);
        $this->assertStringNotContainsString('request model', $go['models/mock.go']);
    }

    /**
     * A released CLI has to build from its own module, so only a CLI pinned
     * to a local Go SDK may replace the published one.
     */
    public function testCliReplacesGoSdkOnlyWhenPinned(): void
    {
        $this->assertStringNotContainsString('replace ', $this->generateCliModule(new CLI(), 'release'));
        $this->assertStringContainsString(
            'replace ' . CLI::SDK_MODULE . ' => ../go',
            $this->generateCliModule(new CLI()->setSdkPath('../go'), 'pinned'),
        );
    }

    /**
     * A repeatable flag given only `[]` has to reach the SDK as an empty array,
     * so every array flag is routed through the CLI's slice helpers.
     */
    public function testCliArrayFlagsCanSendAnEmptyArray(): void
    {
        $dir = self::OUTPUT . '/cli-module/arrays';
        $this->removeDirectory($dir);

        new SDK(new CLI(), Parser::parse((string) \file_get_contents(self::FIXTURE)))
            ->setTest('')
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->generate($dir);

        $unrouted = [];
        foreach (\glob($dir . '/internal/cmd/services/*.go') ?: [] as $path) {
            $source = (string) \preg_replace('/app\.(?:StringSlice|DecodeSlice\[[^\]]+\])\(\w+\)/', '', (string) \file_get_contents($path));
            \preg_match_all('/StringArrayVar\(&(\w+), "(?!filter"|where"|sort-asc"|sort-desc"|select"|queries")/', $source, $flags);
            foreach (\array_unique($flags[1]) as $variable) {
                if (\preg_match('/[(,]\s*' . $variable . '\s*[),]/', $source) === 1) {
                    $unrouted[] = \basename($path) . ': ' . $variable;
                }
            }
        }

        $this->assertNotEmpty(\glob($dir . '/internal/cmd/services/*.go'));
        $this->assertSame([], $unrouted, 'array flags passed to the SDK without app.StringSlice or app.DecodeSlice');
    }

    private function generateCliModule(CLI $language, string $key): string
    {
        $dir = self::OUTPUT . '/cli-module/' . $key;
        $this->removeDirectory($dir);

        new SDK($language, Parser::parse((string) \file_get_contents(self::FIXTURE)))
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->generate($dir);

        return (string) \file_get_contents($dir . '/go.mod');
    }

    public function testWebPublishesToTheCdnOnlyWhenPathIsSet(): void
    {
        $cdn = $this->generate('web', 'client', language: new Web()->setCdnPath('zzcdn'), variant: '-cdn');
        $npm = $this->generate('web', 'client');
        $missing = [];
        foreach (self::CDN_ARTIFACTS as $path => $tokens) {
            foreach ($tokens as $token) {
                if (!\str_contains($cdn[$path] ?? '', $token)) {
                    $missing[] = "{$path}: {$token}";
                }
            }
        }

        $this->assertSame([], $missing, 'web with a CDN path lacks CDN artifacts');

        $leaked = [];
        foreach (['web' => $npm, 'node' => $this->generate('node', 'server'), 'react-native' => $this->generate('react-native', 'client')] as $name => $files) {
            foreach (self::CDN_TOKENS as $token) {
                foreach ($this->filesContaining($files, $token) as $match) {
                    $leaked[] = "{$name}: {$match}: {$token}";
                }
            }
        }

        $this->assertSame([], $leaked, 'a JavaScript SDK without a CDN path renders CDN artifacts');

        foreach (['cdn' => $cdn, 'npm' => $npm] as $mode => $files) {
            $this->assertStringContainsString('terser(', $files['rollup.config.mjs'], "web ({$mode}): rollup.config.mjs does not minify");
        }
    }

    /**
     * The generated tree for an inline document, for a shape the shared
     * fixture does not carry.
     *
     * @param  array<string, mixed>  $document
     * @return array<string, string>
     */
    private function generateDocument(array $document, string $name, string $key): array
    {
        $dir = self::OUTPUT . '/' . $name . '/' . $key;
        $this->removeDirectory($dir);

        new SDK($this->language($name), Parser::parse($document))
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[\substr((string) $file->getPathname(), \strlen($dir) + 1)] = (string) \file_get_contents($file->getPathname());
            }
        }

        return $files;
    }

    /**
     * A path parameter the operation lists in `x-appwrite.config` is filled
     * from the client, not from an argument, and never sent as a header. A
     * document still binding it with a path security scheme generates the
     * same SDK, which is what lets the producer switch without a flag day.
     */
    public function testPathParametersFilledFromClientConfig(): void
    {
        $document = static fn(bool $legacy): array => [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'general']],
            'components' => ['securitySchemes' => \array_filter([
                'Project' => ['type' => 'apiKey', 'name' => 'X-Appwrite-Project', 'in' => 'header', 'x-appwrite' => ['demo' => '<YOUR_PROJECT_ID>']],
                'ProjectPath' => $legacy ? ['type' => 'apiKey', 'name' => 'project', 'in' => 'query', 'x-appwrite' => ['location' => 'path', 'param' => 'project_id', 'demo' => '<YOUR_PROJECT_ID>']] : null,
            ])],
            'paths' => ['/tests/{project_id}/approve' => ['post' => [
                'operationId' => 'generalApprove',
                'tags' => ['general'],
                'summary' => 'Approve',
                'description' => 'Approve.',
                'security' => $legacy ? [['ProjectPath' => []]] : [],
                'parameters' => [
                    ['name' => 'project_id', 'in' => 'path', 'required' => true, 'description' => 'Project ID.', 'schema' => ['type' => 'string']],
                    ['name' => 'project_id', 'in' => 'query', 'required' => true, 'description' => 'Project ID to act on.', 'schema' => ['type' => 'string']],
                    ['name' => 'grant_id', 'in' => 'query', 'required' => true, 'description' => 'Grant ID.', 'schema' => ['type' => 'string']],
                ],
                'responses' => ['204' => ['description' => 'ok']],
                'x-appwrite' => $legacy
                    ? ['auth' => ['server' => ['ProjectPath' => []]]]
                    : ['config' => ['project_id' => 'project'], 'auth' => ['server' => ['Project' => []]]],
            ]]],
        ];

        $files = $this->generateDocument($document(false), 'php', 'path-config');
        $service = $files['src/Appwrite/Services/General.php'];
        \preg_match('/function approve\(([^)]*)\)/', $service, $signature);

        $this->assertStringContainsString("getConfig('project')", $service);
        $this->assertSame(1, \substr_count($signature[1] ?? '', '$projectId'));
        $this->assertStringNotContainsString('X-Appwrite-Project', $service);
        $this->assertSame($files, $this->generateDocument($document(true), 'php', 'path-config-legacy'));
    }

    /**
     * An empty `x-appwrite.config` value names no client setting, so the path
     * parameter stays an argument the caller passes.
     */
    public function testPathParametersWithBlankConfigStayArguments(): void
    {
        $document = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'general']],
            'paths' => ['/tests/{project_id}/approve' => ['post' => [
                'operationId' => 'generalApprove',
                'tags' => ['general'],
                'summary' => 'Approve',
                'description' => 'Approve.',
                'parameters' => [
                    ['name' => 'project_id', 'in' => 'path', 'required' => true, 'description' => 'Project ID.', 'schema' => ['type' => 'string']],
                    ['name' => 'grant_id', 'in' => 'query', 'required' => true, 'description' => 'Grant ID.', 'schema' => ['type' => 'string']],
                ],
                'responses' => ['204' => ['description' => 'ok']],
                'x-appwrite' => ['config' => ['project_id' => '']],
            ]]],
        ];

        $service = $this->generateDocument($document, 'web', 'path-config-blank')['src/services/general.ts'];

        $this->assertStringContainsString('approve(projectId: string, grantId: string)', $service);
    }

    /**
     * A path parameter fills exactly one segment: a `/`, `?`, `#` or `..` in
     * an ID must not reach another endpoint. The fragment is the encoding at
     * the substitution site (getPath) or where a chunked upload resumes.
     */
    #[DataProvider('pathSegmentEncoders')]
    public function testPathParametersFillOneSegment(string $name, string $platform, string $file, string $fragment): void
    {
        $this->assertStringContainsString($fragment, $this->generate($name, $platform)[$file] ?? '');
    }

    /** @return Iterator<string, array{string, string, string, string}> */
    public static function pathSegmentEncoders(): Iterator
    {
        yield 'go resume' => ['go', 'server', 'client/client.go', 'resp, err := client.call("get", url+"/"+encodepath(uploadid), nil, nil)'];
        yield 'python path' => ['python', 'server', 'appwrite/services/general.py', "api_path = api_path.replace('{pathid}', self._encode_path(path_id))"];
        yield 'python encoder' => ['python', 'server', 'appwrite/service.py', "return quote(segment, safe=\"!*'()\")"];
        yield 'python resume' => ['python', 'server', 'appwrite/client.py', "self.call('get', path + '/' + quote(upload_id, safe=\"!*'()\"), headers)"];
        yield 'ruby path' => ['ruby', 'server', 'lib/appwrite/services/general.rb', ".gsub('{pathid}', @client.encode_path(path_id))"];
        yield 'ruby encoder' => ['ruby', 'server', 'lib/appwrite/client.rb', "segment.b.gsub(/[^a-za-z0-9\\-_.!~*'()]/n)"];
        yield 'ruby resume' => ['ruby', 'server', 'lib/appwrite/client.rb', 'path: "#{path}/#{encode_path(upload_id)}",'];
        yield 'php path' => ['php', 'server', 'src/Appwrite/Services/General.php', "['{pathid}'],\n            [self::encodepath(\$pathid)],"];
        yield 'php encoder' => ['php', 'server', 'src/Appwrite/Service.php', "return strtr(rawurlencode(\$segment), ["];
        yield 'dotnet path' => ['dotnet', 'server', 'Appwrite/Services/General.cs', '.replace("{pathid}", client.encodepath(pathid));'];
        yield 'dotnet encoder' => ['dotnet', 'server', 'Appwrite/Client.cs', "return uri.escapedatastring(value ?? string.empty)\n                .replace(\"%21\", \"!\")"];
        yield 'dotnet resume' => ['dotnet', 'server', 'Appwrite/Client.cs', 'var uploadpath = encodepath(uploadid);'];
        yield 'unity path' => ['unity', 'client', 'Runtime/Core/Services/General.cs', '.replace("{pathid}", client.encodepath(pathid));'];
        yield 'unity resume' => ['unity', 'client', 'Runtime/Core/Client.cs', 'var uploadpath = encodepath(parameters[idparamname!]?.tostring());'];
        yield 'kotlin path' => ['kotlin', 'server', 'src/main/kotlin/io/appwrite/services/General.kt', '.replace("{pathid}", client.encodepath(pathid))'];
        yield 'kotlin encoder' => ['kotlin', 'server', 'src/main/kotlin/io/appwrite/Client.kt', "urlencoder\n                .encode(value, \"utf-8\")\n                .replace(\"+\", \"%20\")"];
        yield 'kotlin resume' => ['kotlin', 'server', 'src/main/kotlin/io/appwrite/Client.kt', 'path = "$path/${encodepath(provideduploadid)}",'];
        yield 'android path' => ['android', 'client', 'library/src/main/java/io/appwrite/services/General.kt', '.replace("{pathid}", client.encodepath(pathid))'];
        yield 'android encoder' => ['android', 'client', 'library/src/main/java/io/appwrite/Client.kt', "urlencoder\n                .encode(value, \"utf-8\")\n                .replace(\"+\", \"%20\")"];
        yield 'android resume' => ['android', 'client', 'library/src/main/java/io/appwrite/Client.kt', 'path = "$path/${encodepath(provideduploadid)}",'];
        foreach (['swift' => 'server', 'apple' => 'client'] as $name => $platform) {
            yield "{$name} path" => [$name, $platform, 'Sources/Appwrite/Services/General.swift', '.replacingoccurrences(of: "{pathid}", with: try client.encodepath(pathid))'];
            yield "{$name} encoder" => [$name, $platform, 'Sources/Appwrite/Client.swift', "charactersin: \"abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz0123456789-_.!~*'()\""];
            yield "{$name} resume" => [$name, $platform, 'Sources/Appwrite/Client.swift', 'let uploadpath = try client.encodepath(uploadid)'];
        }
        yield 'rust path' => ['rust', 'server', 'src/services/general.rs', '.replace("{pathid}", &client::encode_path(&path_id))'];
        yield 'rust encoder' => ['rust', 'server', 'src/client.rs', "if byte.is_ascii_alphanumeric() || b\"-_.!~*'()\".contains(&byte) {"];
        yield 'rust resume' => ['rust', 'server', 'src/client.rs', 'let resume_path = format!("{}/{}", path, self::encode_path(id));'];
        foreach (['dart' => 'server', 'flutter' => 'client'] as $name => $platform) {
            yield "{$name} path" => [$name, $platform, 'lib/services/general.dart', "'/mock/tests/general/path/{pathid}'.replaceall(\n      '{pathid}',\n      _encodepath(pathid),"];
            yield "{$name} resume io" => [$name, $platform, 'lib/src/client_io.dart', "if (idvalue == '.' || idvalue == '..') {"];
            yield "{$name} resume browser" => [$name, $platform, 'lib/src/client_browser.dart', "if (idvalue == '.' || idvalue == '..') {"];
        }
    }

    /**
     * PHP resumes a chunked upload from its service method: it asks for the
     * chunks already uploaded at the path plus the upload ID, which fills
     * one segment like any path parameter.
     */
    public function testPhpUploadResumeFillsOneSegment(): void
    {
        $document = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'storage']],
            'paths' => ['/storage/buckets/{bucketId}/files' => ['post' => [
                'operationId' => 'storageCreateFile',
                'tags' => ['storage'],
                'summary' => 'Create file',
                'description' => 'Create file.',
                'parameters' => [
                    ['name' => 'bucketId', 'in' => 'path', 'required' => true, 'description' => 'Bucket ID.', 'schema' => ['type' => 'string']],
                ],
                'requestBody' => ['content' => ['multipart/form-data' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'fileId' => ['type' => 'string', 'description' => 'File ID.'],
                        'file' => ['type' => 'string', 'format' => 'binary', 'description' => 'File.'],
                    ],
                    'required' => ['fileId', 'file'],
                ]]]],
                'responses' => ['204' => ['description' => 'ok']],
            ]]],
        ];

        $service = $this->generateDocument($document, 'php', 'upload-resume')['src/Appwrite/Services/Storage.php'];

        $this->assertStringContainsString("Client::METHOD_GET, \$apiPath . '/' . self::encodePath(\$fileId));", $service);
        $this->assertStringNotContainsString("\$apiPath . '/' . \$fileId", $service);
    }

    /**
     * A server SDK sends the API key with every call, so it must not follow a
     * redirect, which may point at another host, and must not keep cookies,
     * which would carry one caller's session into the next call. The first
     * fragment must be in the client, the second must not.
     */
    #[DataProvider('serverClientTransports')]
    public function testServerClientsNeitherFollowRedirectsNorKeepCookies(string $name, string $file, string $present, string $absent): void
    {
        $client = $this->generate($name, 'server')[$file] ?? '';

        $this->assertStringContainsString($present, $client);
        if ($absent !== '') {
            $this->assertStringNotContainsString($absent, $client);
        }
    }

    /** @return Iterator<string, array{string, string, string, string}> */
    public static function serverClientTransports(): Iterator
    {
        yield 'go redirects' => ['go', 'client/client.go', "checkredirect: func(*http.request, []*http.request) error {\n\t\t\treturn http.erruselastresponse", ''];
        yield 'go redirect error' => ['go', 'client/client.go', 'message:    "redirect to " + resp.header.get("location") + " not followed",', ''];
        yield 'go cookies' => ['go', 'client/client.go', 'timeout: timeout,', 'cookiejar'];
        yield 'python redirects' => ['python', 'appwrite/client.py', "allow_redirects=false,\n", "allow_redirects=false if"];
        yield 'python redirect error' => ['python', 'appwrite/client.py', "if response_type != 'location' and 300 <= response.status_code < 400:", ''];
        yield 'php redirects' => ['php', 'src/Appwrite/Client.php', 'new httpclient($adapter)->withfollowredirects(false);', 'httpclientnoredirect'];
        yield 'php redirect error' => ['php', 'src/Appwrite/Client.php', "throw new appwriteexception('redirect to ' . \$location . ' not followed', \$responsestatus, '', \$responsebody);", ''];
        yield 'dotnet handler' => ['dotnet', 'Appwrite/Client.cs', "allowautoredirect = false,\n                usecookies = false", '_http = http ?? new httpclient();'];
        yield 'dotnet redirect error' => ['dotnet', 'Appwrite/Client.cs', 'throw new appwriteexception($"redirect to {location} not followed", code, "", body);', ''];
        yield 'kotlin redirects' => ['kotlin', 'src/main/kotlin/io/appwrite/Client.kt', ".newbuilder()\n            .followredirects(false)\n            .followsslredirects(false)", ''];
        yield 'kotlin redirect error' => ['kotlin', 'src/main/kotlin/io/appwrite/Client.kt', 'it.cancel(appwriteexception("redirect to $location not followed", response.code, "", body))', ''];
        yield 'swift redirects' => ['swift', 'Sources/Appwrite/Client.swift', 'redirectconfiguration: httpclient.configuration.redirectconfiguration = .disallow,', 'request.adddomaincookies()'];
        yield 'swift redirect error' => ['swift', 'Sources/Appwrite/Client.swift', "case 300..<400:\n            let location = response.headers[\"location\"].first ?? \"\"", ''];
        yield 'dart redirects' => ['dart', 'lib/src/client_io.dart', "params: params,\n    );\n    // not followed: a redirect would carry the api key wherever it points.\n    request.followredirects = false;", ''];
        yield 'dart redirect error' => ['dart', 'lib/src/client_mixin.dart', "if (res.statuscode >= 300 && res.statuscode < 400) {", ''];
        yield 'node redirects' => ['node', 'src/client.ts', "const response = await fetch(uri, {\n            ...options,\n            redirect: 'manual',\n        });\n\n        const warnings", ''];
        yield 'node redirect error' => ['node', 'src/client.ts', '`redirect to ${location} not followed`,', ''];
        yield 'rust redirects' => ['rust', 'src/client.rs', "fn build_http_client(config: &config) -> httpclient {\n        let mut builder = httpclient::builder()\n            .redirect(reqwest::redirect::policy::none())", ''];
        yield 'rust redirect error' => ['rust', 'src/client.rs', 'format!("redirect to {} not followed", location),', ''];
        yield 'ruby redirects' => ['ruby', 'lib/appwrite/client.rb', 'raise appwrite::exception.new("redirect to #{location} not followed", response.code, nil, response.body)', 'return fetch(method, uri'];
    }

    /**
     * A file that fits in one request is sent with the same headers as each
     * chunk of a larger one. The project header comes from those headers, and
     * without it a project API key is refused.
     */
    public function testPhpSingleRequestUploadSendsTheMethodHeaders(): void
    {
        $service = $this->generate('php', 'server')['src/Appwrite/Services/General.php'];
        $upload = \explode('public function optionalupload(', \explode('public function upload(', $service, 2)[1] ?? '', 2)[0];

        $this->assertStringContainsString("\$apiheaders['x-appwrite-project'] = \$this->client->getconfig('project');", $upload);
        $this->assertStringContainsString('$response = $this->client->call(client::method_post, $apipath, $apiheaders, $apiparams);', $upload);
        $this->assertStringNotContainsString('$apipath, [', $service);
    }

    /**
     * An upload whose only parameter is the file still takes the progress
     * callback as its second positional argument.
     */
    public function testSingleParameterUploadTakesProgressCallback(): void
    {
        foreach (['web', 'node', 'react-native'] as $name) {
            $service = $this->generate($name, 'client')['src/services/general.ts'];

            $this->assertMatchesRegularExpression('/uploadfileonly\(\s*file: [^,]+,\s*onprogress\?: \(progress: uploadprogress\) => void,\s*\): promise/', $service, "{$name} uploadFileOnly lacks a positional onProgress");
        }
    }

    /**
     * A generated service test pins the request its method sends: the verb,
     * the path with the example arguments substituted, and the parameter
     * names. Asserting only the returned model passes even when the method
     * calls the wrong endpoint or drops its body.
     */
    #[DataProvider('serviceTestRequests')]
    public function testServiceTestsAssertTheRequest(string $name, string $platform, string $file, string $fragment): void
    {
        $this->assertStringContainsString($fragment, $this->generate($name, $platform)[$file] ?? '');
    }

    /** @return Iterator<string, array{string, string, string, string}> */
    public static function serviceTestRequests(): Iterator
    {
        yield 'php path' => ['php', 'server', 'tests/Appwrite/Services/GeneralTest.php', "->expects()->call(\n                'get',\n                '/mock/tests/general/path/grant%2fspecial%26id',"];
        yield 'php params' => ['php', 'server', 'tests/Appwrite/Services/FooTest.php', "->expects()->call(\n                'put',\n                '/mock/tests/foo',\n                mockery::any(),\n                mockery::on(function (array \$params): bool {\n                    \$this->assertsame(['x', 'y', 'z'], array_keys(\$params));"];
        yield 'python path' => ['python', 'server', 'test/services/test_general.py', "response = self.general.get_path(\n            'grant/special&id',\n        )\n        request = m.last_request\n        url = urlsplit(request.url)\n        self.assertequal(\n            unquote(url.path),\n            '/v1/mock/tests/general/path/grant/special&id',\n        )"];
        yield 'python params' => ['python', 'server', 'test/services/test_foo.py', "m.request(\n            'put',\n            requests_mock.any,"];
        yield 'python body' => ['python', 'server', 'test/services/test_foo.py', "sent = json.loads(request.body or '{}')\n        self.assertequal(\n            sent,\n            {\n                'x': '[]',\n                'y': 1,\n                'z': [],\n            },\n        )"];
        foreach (['dart' => 'server', 'flutter' => 'client'] as $name => $platform) {
            yield "{$name} path" => [$name, $platform, 'test/services/general_test.dart', "verify(\n        client.call(\n          httpmethod.get,\n          path: \"/mock/tests/general/path/grant%2fspecial%26id\",\n          headers: anynamed('headers'),\n          params: captureanynamed('params'),"];
            yield "{$name} params" => [$name, $platform, 'test/services/foo_test.dart', "client.call(\n          httpmethod.put,\n          path: \"/mock/tests/foo\",\n          headers: anynamed('headers'),\n          params: captureanynamed('params'),\n          responsetype: anynamed('responsetype'),\n        ),\n      ).captured.single;\n      expect(params, {\n        \"x\": \"[]\",\n        \"y\": 1,\n        \"z\": isa<list>(),\n      });"];
        }
    }

    /**
     * A generated service test passes every optional parameter and pins the
     * value sent for it. Calling with the required parameters only passes
     * even when the method drops an optional parameter, or drops a boolean
     * one when it is false.
     */
    #[DataProvider('serviceTestOptionalParameters')]
    public function testServiceTestsPassOptionalParameters(string $name, string $platform, string $file, string $fragment): void
    {
        $this->assertStringContainsString($fragment, $this->generate($name, $platform)[$file] ?? '');
    }

    /** @return Iterator<string, array{string, string, string, string}> */
    public static function serviceTestOptionalParameters(): Iterator
    {
        yield 'php arguments' => ['php', 'server', 'tests/Appwrite/Services/FunctionsTest.php', "\$response = \$this->functions->createexecution(\n            \"functionid\",\n            body: \"body\",\n            async: false,"];
        yield 'php body' => ['php', 'server', 'tests/Appwrite/Services/FunctionsTest.php', "\$expected = [\n                        'functionid' => \"functionid\",\n                        'body' => \"body\",\n                        'async' => false,"];
        yield 'php query' => ['php', 'server', 'tests/Appwrite/Services/GeneralTest.php', "\$expected = [\n                        'width' => 1,\n                        'height' => 1,\n                        'name' => \"name\",\n                    ];\n                    \$this->assertsame(\$expected, array_intersect_key(\$params, \$expected));"];
        yield 'python arguments' => ['python', 'server', 'test/services/test_functions.py', "response = self.functions.create_execution(\n            'functionid',\n            body='body',\n            xasync=false,"];
        yield 'python body' => ['python', 'server', 'test/services/test_functions.py', "sent = json.loads(request.body or '{}')\n        self.assertequal(\n            sent,\n            {\n                'body': 'body',\n                'async': false,"];
        yield 'python query' => ['python', 'server', 'test/services/test_general.py', "sent = parse_qs(url.query, keep_blank_values=true)\n        self.assertequal(\n            sent,\n            {\n                'width': ['1'],\n                'height': ['1'],\n                'name': ['name'],\n            },\n        )"];
        yield 'python multipart' => ['python', 'server', 'test/services/test_general.py', "self.assertequal(\n            sent,\n            {\n                'message': 'message',\n                'attachment': '',\n            },\n        )"];
        yield 'dart arguments' => ['dart', 'server', 'test/services/functions_test.dart', "functions.createexecution(\n        functionid: \"functionid\",\n        body: \"body\",\n        xasync: false,"];
        yield 'dart body' => ['dart', 'server', 'test/services/functions_test.dart', "expect(params, {\n        \"body\": \"body\",\n        \"async\": false,"];
        foreach (['dart' => 'server', 'flutter' => 'client'] as $name => $platform) {
            yield "{$name} query arguments" => [$name, $platform, 'test/services/general_test.dart', "general.getoptional(\n        width: 1,\n        height: 1,\n        name: \"name\",\n      );"];
            yield "{$name} query" => [$name, $platform, 'test/services/general_test.dart', "expect(params, {\n        \"width\": 1,\n        \"height\": 1,\n        \"name\": \"name\",\n      });"];
        }
    }

    public function testJavascriptResponseDefaultsFitThePrintWidth(): void
    {
        $document = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'tags' => [['name' => 'zzwidth']],
            'paths' => ['/zzwidth' => ['get' => [
                'operationId' => 'zzwidthGet',
                'tags' => ['zzwidth'],
                'summary' => 'Get',
                'description' => 'Get.',
                'responses' => ['200' => ['description' => 'ok', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/zzwide']]]]],
            ]]],
            'components' => ['schemas' => ['zzwide' => [
                'type' => 'object',
                'required' => ['zzstatements', 'zzports'],
                'properties' => [
                    'zzstatements' => ['type' => 'array', 'items' => ['type' => 'string'], 'default' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'ALTER', 'CREATE', 'TRUNCATE']],
                    'zzports' => ['type' => 'array', 'items' => ['type' => 'integer'], 'default' => [8080, 8081, 8082, 8083, 8084, 8085, 8086, 8087, 8088, 8089, 8090, 8091]],
                ],
            ]]],
        ];

        $service = $this->generateDocument($document, 'node', 'width')['src/services/zzwidth.ts'] ?? '';

        $this->assertStringContainsString("zzstatements: [\n                            'SELECT',\n", $service);
        $this->assertLessThanOrEqual(80, \max(\array_map(mb_strlen(...), \explode("\n", $service))));
    }

    public function testWebNestedResponseDefaultsAreApplied(): void
    {
        $dir = self::OUTPUT . '/web/defaults';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $preset = $document->paths->{'/mock/tests/general/preset'}->get;
        $reference = (object) ['$ref' => '#/components/schemas/preset'];
        $document->components->schemas->preset->properties->parent = $reference;
        $document->components->schemas->presetList = (object) [
            'type' => 'object',
            'properties' => (object) [
                'total' => (object) ['type' => 'integer'],
                'presets' => (object) ['type' => 'array', 'items' => $reference],
            ],
            'required' => ['total', 'presets'],
        ];

        $list = \json_decode(\json_encode($preset, JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
        $list->operationId = 'generalListPresets';
        $list->responses->{'200'}->content->{'application/json'}->schema = (object) ['$ref' => '#/components/schemas/presetList'];
        $document->paths->{'/mock/tests/general/presets'} = (object) ['get' => $list];

        $array = \json_decode(\json_encode($preset, JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
        $array->operationId = 'generalListPresetArray';
        $array->responses->{'200'}->content->{'application/json'}->schema = (object) ['type' => 'array', 'items' => $reference];
        $document->paths->{'/mock/tests/general/preset-array'} = (object) ['get' => $array];

        $twice = \json_decode(\json_encode($preset, JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
        $twice->operationId = 'generalCreatePreset';
        $twice->responses->{'201'} = $twice->responses->{'200'};
        $twice->responses->{'400'} = (object) [
            'description' => 'Error',
            'content' => (object) ['application/json' => (object) ['schema' => (object) ['$ref' => '#/components/schemas/error']]],
        ];
        $document->paths->{'/mock/tests/general/preset-twice'} = (object) ['get' => $twice];

        $sdk = new SDK($this->language('web'), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('client')
            ->setTest('true')
            ->generate($dir);

        $service = (string) \file_get_contents($dir . '/src/services/general.ts');

        $this->assertStringNotContainsString('...response', $service);
        $this->assertSame(6, \substr_count($service, 'return request.then((response) => applyDefaults(response, defaults));'));
        $this->assertSame(2, \substr_count($service, <<<'TS'
                    const defaults = {
                        root: 'preset',
                        models: {
                            preset: {
                                defaults: {
                                    name: 'guest',
                                    count: 25,
                                    enabled: true,
                                    tier: 'free',
                                    tags: [],
                                    settings: {},
                                },
                                properties: {
                                    parent: 'preset',
                                },
                            },
                        },
                    };
            TS));
        $this->assertStringContainsString(<<<'TS'
                    const defaults = {
                        root: 'presetList',
                        models: {
                            presetList: {
                                defaults: {
                                    presets: [],
                                },
                                properties: {
                                    presets: {
                                        items: 'preset',
                                    },
                                },
                            },
                            preset: {
                                defaults: {
                                    name: 'guest',
                                    count: 25,
                                    enabled: true,
                                    tier: 'free',
                                    tags: [],
                                    settings: {},
                                },
                                properties: {
                                    parent: 'preset',
                                },
                            },
                        },
                    };
            TS, $service);
        $this->assertStringContainsString(<<<'TS'
                    const defaults = {
                        root: {
                            items: 'preset',
                        },
                        models: {
                            preset: {
                                defaults: {
                                    name: 'guest',
                                    count: 25,
                                    enabled: true,
                                    tier: 'free',
                                    tags: [],
                                    settings: {},
                                },
                                properties: {
                                    parent: 'preset',
                                },
                            },
                        },
                    };
            TS, $service);
    }

    public function testRubyModelDefaultsAreLiterals(): void
    {
        $dir = self::OUTPUT . '/ruby/defaults';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $document->components->schemas->mock->properties->optionalResult->default = 'pending';
        $document->components->schemas->mock->properties->metadataList->default = [(object) ['enabled' => null, 'label' => '#{name}']];
        $document->components->schemas->mock->properties->settings = (object) ['type' => 'object', 'additionalProperties' => true, 'default' => (object) ['0' => 'x']];
        $document->components->schemas->mock->properties->relatedMock->default = (object) [];
        $document->components->schemas->mock->required[] = 'relatedMock';
        $document->components->schemas->mock->properties->enabled = (object) ['type' => 'boolean', 'default' => false];

        $sdk = new SDK($this->language('ruby'), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $model = (string) \file_get_contents($dir . '/lib/appwrite/models/mock.rb');

        $this->assertStringContainsString('optional_result: "pending"', $model);
        $this->assertStringContainsString('metadata_list: [{ "enabled" => nil, "label" => "\\#{name}" }]', $model);
        $this->assertStringContainsString('settings: { "0" => "x" }', $model);
        $this->assertStringContainsString('map = { "relatedMock" => {} }.merge(map)', $model);
        $this->assertStringContainsString('related_mock: UnionMock.from(map: map["relatedMock"])', $model);
        $this->assertStringContainsString('enabled: false', $model);
        $this->assertStringContainsString('status: nil', $model);
    }

    public function testPythonModelDefaultsAreValidated(): void
    {
        $dir = self::OUTPUT . '/python/defaults';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $document->components->schemas->mock->required[] = 'localizedStatus';
        $document->components->schemas->mock->properties->localizedStatus->default = 'ខេត្ត';
        $document->components->schemas->zznullablescalars->properties->count->default = 0;
        $document->components->schemas->zznullablescalars->properties->price->default = 0.0;

        $sdk = new SDK($this->language('python'), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $model = (string) \file_get_contents($dir . '/appwrite/models/mock.py');
        $scalars = (string) \file_get_contents($dir . '/appwrite/models/zznullablescalars.py');

        $this->assertStringContainsString("localizedstatus: LocalizedStatus = Field(..., alias='localizedStatus')", $model);
        $this->assertStringContainsString("@model_validator(mode='before')", $model);
        $this->assertStringContainsString("if 'localizedStatus' not in data and 'localizedstatus' not in data:\n            data['localizedStatus'] = 'ខេត្ត'", $model);
        $this->assertStringContainsString("data['count'] = 0\n", $scalars);
        $this->assertStringContainsString("data['price'] = 0.0\n", $scalars);
    }

    public function testDotNetModelDefaultsAreLiterals(): void
    {
        $dir = self::OUTPUT . '/dotnet/defaults';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $document->components->schemas->mock->required[] = 'metadataList';
        $document->components->schemas->mock->properties->result->default = 'say "hi" \\ {0}';
        $document->components->schemas->mock->properties->metadataList->default = [(object) ['enabled' => true, 'label' => 'x'], []];

        $sdk = new SDK($this->language('dotnet'), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $model = (string) \file_get_contents($dir . '/Appwrite/Models/Mock.cs');

        $this->assertStringContainsString('result: (map.ContainsKey("result") ? map["result"] : "say \"hi\" \\\\ {0}").ToString()', $model);
        $this->assertStringContainsString('(map.ContainsKey("metadataList") ? map["metadataList"] : new List<object> { new Dictionary<string, object> { { "enabled", true }, { "label", "x" } }, new List<object>() }).ConvertToList<', $model);
    }

    #[DataProvider('kotlinLanguages')]
    public function testKotlinModelDefaultsFollowTheSchema(string $name, string $platform): void
    {
        $document = \json_decode((string) \file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR);
        $preset = &$document['components']['schemas']['preset'];
        $preset['properties']['settings'] = ['type' => 'object', 'additionalProperties' => true, 'default' => (object) []];
        $preset['properties']['mock'] = ['$ref' => '#/components/schemas/mock', 'default' => ['result' => 'ok']];
        $preset['properties']['mocks'] = ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/mock'], 'default' => [['result' => 'ok']]];
        $preset['required'][] = 'settings';
        $preset['required'][] = 'mock';
        $preset['required'][] = 'mocks';

        $files = $this->generateDocument($document, $name, 'defaults');
        $path = $name === 'android' ? 'library/src/main/java/appwrite/models/Preset.kt' : 'src/main/kotlin/appwrite/models/Preset.kt';
        $model = $files[$path];

        $this->assertStringContainsString('settings = (if (map.containsKey("settings")) map["settings"] else mapOf<String, Any?>()) as Map<String, Any?>', $model, "{$name}/{$platform}");
        $this->assertStringContainsString('mock = Mock.from(map = (if (map.containsKey("mock")) map["mock"] else mapOf<String, Any?>("result" to "ok")) as Map<String, Any>)', $model, "{$name}/{$platform}");
        $this->assertStringContainsString('mocks = ((if (map.containsKey("mocks")) map["mocks"] else listOf<Any?>(mapOf<String, Any?>("result" to "ok"))) as List<Map<String, Any>>).map { Mock.from(map = it) }', $model, "{$name}/{$platform}");
    }

    public function testModelDefaultsFollowTheTargetLiteralSyntax(): void
    {
        $document = \json_decode((string) \file_get_contents(self::FIXTURE), true, flags: JSON_THROW_ON_ERROR);
        $document['components']['schemas']['mock']['required'][] = 'settings';
        $document['components']['schemas']['mock']['required'][] = 'metadataList';
        $document['components']['schemas']['mock']['properties']['metadataList']['default'] = [];
        $document['components']['schemas']['mock']['properties']['result']['default'] = "a\u{8}b";
        $document['components']['schemas']['mock']['properties']['settings'] = [
            'type' => 'object',
            'description' => 'Settings.',
            'additionalProperties' => true,
            'default' => [],
        ];

        $rust = $this->generateDocument($document, 'rust', 'defaults');
        $dotnet = $this->generateDocument($document, 'dotnet', 'defaults');
        $dart = $this->generateDocument($document, 'dart', 'defaults');

        $this->assertStringContainsString('"a\\u{08}b".to_string()', $rust['src/models/mock.rs']);
        $this->assertStringContainsString('fn default_metadata_list() -> Vec<serde_json::Value> {', $rust['src/models/mock.rs']);
        $this->assertStringContainsString('map["settings"] : new Dictionary<string, object>())', $dotnet['Appwrite/Models/Mock.cs']);
        $this->assertStringContainsString("'settings': <String, dynamic>{},", $dart['lib/src/models/mock.dart']);
    }

    public function testSwiftModelDefaultsAreLiterals(): void
    {
        $dir = self::OUTPUT . '/swift/defaults';
        $this->removeDirectory($dir);

        $document = \json_decode((string) \file_get_contents(self::FIXTURE), flags: JSON_THROW_ON_ERROR);
        $mock = $document->components->schemas->mock;
        $mock->required = [...$mock->required, 'status', 'relatedMock', 'metadataList', 'zzsettings', 'zzratio', 'zzweight', 'zzmocks'];
        $mock->properties->status->default = 'active';
        $mock->properties->relatedMock->default = (object) ['type' => 'mock', 'result' => 'ok'];
        $mock->properties->metadataList->default = [(object) ['enabled' => null, 'label' => 'x', 'ratio' => 1.0], (object) []];
        $mock->properties->zzsettings = (object) ['type' => 'object', 'additionalProperties' => true, 'default' => (object) []];
        $mock->properties->zzratio = (object) ['type' => 'number', 'default' => 1];
        $mock->properties->zzweight = (object) ['type' => 'number', 'default' => 2.0];
        $mock->properties->result->default = 'C:\\temp "x"';
        $mock->properties->zzmocks = (object) ['type' => 'array', 'items' => (object) ['$ref' => '#/components/schemas/unionMock'], 'default' => [(object) ['type' => 'mock', 'result' => 'ok']]];

        $sdk = new SDK($this->language('swift'), Parser::parse(\json_encode($document, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)));
        $sdk
            ->setName('test')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setTest('true')
            ->generate($dir);

        $model = (string) \file_get_contents($dir . '/Sources/AppwriteModels/Mock.swift');

        $this->assertStringContainsString(<<<'SWIFT'
                    let defaults: [String: Any] = [
                        "result": "C:\\temp \"x\"",
                        "status": "active",
                        "relatedMock": ["type": "mock", "result": "ok"] as [String: Any],
                        "metadataList": [["enabled": NSNull(), "label": "x", "ratio": 1.0] as [String: Any], [Any]()] as [Any],
                        "zzsettings": [String: Any](),
                        "zzratio": 1.0,
                        "zzweight": 2.0,
                        "zzmocks": [["type": "mock", "result": "ok"] as [String: Any]] as [Any],
                    ]
                    let map = defaults.merging(map) { _, new in new }
            SWIFT, $model);
        $this->assertStringContainsString('relatedMock: UnionMock.from(map: map["relatedMock"] as! [String: Any])', $model);
        $this->assertStringContainsString('self.relatedMock = try container.contains(.relatedMock) ? container.decode(UnionMock.self, forKey: .relatedMock) : UnionMock.from(map: ["type": "mock", "result": "ok"] as [String: Any])', $model);
        $this->assertStringContainsString('self.metadataList = try container.contains(.metadataList) ? container.decode([AnyCodable].self, forKey: .metadataList) : ([["enabled": NSNull(), "label": "x", "ratio": 1.0] as [String: Any], [Any]()] as [Any]).map { AnyCodable($0) }', $model);
        $this->assertStringContainsString('self.zzmocks = try container.contains(.zzmocks) ? container.decode([UnionMock].self, forKey: .zzmocks) : ([["type": "mock", "result": "ok"] as [String: Any]] as [Any]).map { UnionMock.from(map: $0 as! [String: Any]) }', $model);
        $this->assertStringContainsString('self.zzsettings = try container.contains(.zzsettings) ? container.decode([String: AnyCodable].self, forKey: .zzsettings) : ([String: Any]()).mapValues { AnyCodable($0) }', $model);
    }

    /** @return iterable<string, array{string}> */
    public static function skillLanguages(): iterable
    {
        foreach (\array_keys(self::SKILL_LANGUAGES) as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('skillLanguages')]
    public function testSkillNamesMatchTheirDirectories(string $name): void
    {
        $skills = \array_filter($this->generate($name, 'console'), static fn(string $path): bool => \basename($path) === 'SKILL.md', ARRAY_FILTER_USE_KEY);
        $this->assertNotEmpty($skills);

        foreach ($skills as $path => $contents) {
            $this->assertSame(1, \preg_match('/\\A---\\nname: (\\S+)\\n/', $contents, $match), "{$name}: {$path} has no frontmatter name");
            $this->assertSame(\basename(\dirname($path)), $match[1], "{$name}: {$path}");
        }
    }

    public function testGoStringArraysKeepTheirSchemaTypeWhenDescriptionsMentionAttributes(): void
    {
        foreach (['List of attributes to export.', 'Collection attributes to export.'] as $description) {
            $document = [
                'openapi' => '3.0.0',
                'info' => ['title' => 'test', 'version' => '1.0.0'],
                'tags' => [['name' => 'migrations']],
                'paths' => ['/exports' => ['post' => [
                    'operationId' => 'migrationsCreateExport',
                    'tags' => ['migrations'],
                    'requestBody' => ['content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'columns' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => $description],
                            'definitions' => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Collection attributes'],
                        ],
                    ]]]],
                    'responses' => ['200' => ['description' => 'ok']],
                ]]],
            ];
            $files = $this->generateDocument($document, 'go', 'export-columns');
            $service = $files['migrations/migrations.go'];
            $this->assertStringContainsString('WithCreateExportColumns(v []string)', $service);
            $this->assertStringContainsString('WithCreateExportDefinitions(v []map[string]any)', $service);
        }
    }

    public function testGoModelCommentsAreNotHtmlEscaped(): void
    {
        $models = \array_filter($this->generate('go', 'server'), static fn(string $path): bool => \str_starts_with($path, 'models/') && \str_ends_with($path, '.go'), ARRAY_FILTER_USE_KEY);
        $this->assertNotEmpty($models);

        foreach ($models as $path => $contents) {
            foreach (['&quot;', '&#039;', '&amp;', '&lt;', '&gt;'] as $entity) {
                $this->assertStringNotContainsString($entity, $contents, "HTML entity {$entity} leaked into go: {$path}");
            }
        }
    }

    #[DataProvider('languages')]
    public function testEnumKeysAreValid(string $name): void
    {
        $specification = Parser::parse([
            'openapi' => '3.1.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/test' => ['get' => [
                'operationId' => 'testEnumKeys',
                'parameters' => [
                    ['name' => 'localized', 'in' => 'query', 'schema' => [
                        'type' => 'string',
                        'enum' => ['រាជធានី', 'ខេត្ត', 'Test'],
                    ]],
                    ['name' => 'annotated', 'in' => 'query', 'schema' => [
                        'title' => 'ProvinceType',
                        'oneOf' => [
                            ['const' => 'រាជធានី', 'title' => 'Capital'],
                            ['const' => 'ខេត្ត', 'title' => 'Province'],
                            ['const' => 'Test', 'title' => 'Test'],
                        ],
                    ]],
                    ['name' => 'unsafe', 'in' => 'query', 'schema' => [
                        'type' => 'string',
                        'enum' => ['123', '-'],
                    ]],
                ],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
        ]);
        [$localized, $annotated, $unsafe] = $specification->paths['/test']->operations['get']->parameters;
        $language = $this->language($name);

        $this->assertSame(['Value1', 'Value2', 'Test'], $language->resolveEnumKeys($localized));
        $this->assertSame(['Capital', 'Province', 'Test'], $language->resolveEnumKeys($annotated));
        $this->assertSame(['Value123', 'Value2'], $language->resolveEnumKeys($unsafe));
    }

    #[DataProvider('languages')]
    public function testOpenEnumsAllowAnyString(string $name): void
    {
        $enum = [
            'title' => 'WebhookEvent',
            'oneOf' => [
                ['const' => 'user.created', 'title' => 'UserCreated'],
                ['const' => 'user.updated', 'title' => 'UserUpdated'],
            ],
        ];
        $specification = Parser::parse([
            'openapi' => '3.1.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/test' => ['get' => [
                'operationId' => 'testOpenEnums',
                'parameters' => [
                    ['name' => 'plainScalar', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'closedScalar', 'in' => 'query', 'schema' => $enum],
                    ['name' => 'openScalar', 'in' => 'query', 'schema' => ['anyOf' => [$enum, ['type' => 'string']]]],
                    ['name' => 'plainArray', 'in' => 'query', 'schema' => ['type' => 'array', 'items' => ['type' => 'string']]],
                    ['name' => 'closedArray', 'in' => 'query', 'schema' => ['type' => 'array', 'items' => $enum]],
                    ['name' => 'openArray', 'in' => 'query', 'schema' => ['type' => 'array', 'items' => ['anyOf' => [$enum, ['type' => 'string']]]]],
                ],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
        ]);
        [$plainScalar, $closedScalar, $openScalar, $plainArray, $closedArray, $openArray] = $specification->paths['/test']->operations['get']->parameters;
        $language = $this->language($name);
        $permission = '["read(\\"any\\")"]';
        $this->assertTrue($language->isPermissionString($permission));
        $this->assertSame([[
            'action' => 'read',
            'role' => 'any',
            'id' => null,
            'innerRole' => null,
        ]], $language->extractPermissionParts($permission));
        foreach ([$closedScalar, $closedArray, $openScalar, $openArray] as $parameter) {
            $enumSchema = $language->getEnumSchema($parameter);
            $this->assertInstanceOf(StringSchema::class, $enumSchema);
            $this->assertSame(['user.created', 'user.updated'], $enumSchema->enum);
            $this->assertSame(['UserCreated', 'UserUpdated'], $enumSchema->enumKeys);
            $this->assertSame('WebhookEvent', $enumSchema->enumName);
        }
        $this->assertFalse($language->isOpenStringEnum($closedScalar));
        $this->assertFalse($language->isOpenStringEnum($closedArray));
        $this->assertTrue($language->isOpenStringEnum($openScalar));
        $this->assertTrue($language->isOpenStringEnum($openArray));
        $this->assertTrue($language->usesEnumType($closedScalar));
        $this->assertTrue($language->usesEnumType($closedArray));
        $this->assertSame($language->keepsOpenEnumType(), $language->usesEnumType($openScalar));
        $this->assertSame($language->keepsOpenEnumType(), $language->usesEnumType($openArray));
        $this->assertStringContainsString('user.created', $language->getSuggestedEnumExample($openScalar));
        $this->assertStringContainsString('user.created', $language->getSuggestedEnumExample($openArray));
        if ($language instanceof HTTP) {
            return;
        }
        if ($language->keepsOpenEnumType()) {
            foreach ([[$closedScalar, $openScalar], [$closedArray, $openArray]] as [$closed, $open]) {
                $this->assertStringContainsString('WebhookEvent', $language->getTypeName($open, $specification));
                $this->assertNotSame($language->getTypeName($closed, $specification), $language->getTypeName($open, $specification));
            }

            return;
        }

        $this->assertSame(
            $language->getTypeName($plainScalar, $specification),
            $language->getTypeName($openScalar, $specification),
        );
        $this->assertSame(
            $language->getTypeName($plainArray, $specification),
            $language->getTypeName($openArray, $specification),
        );
    }
}
