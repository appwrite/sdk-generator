<?php

declare(strict_types=1);

namespace Tests\Generation;

use Iterator;
use Appwrite\SDK\Language;
use Appwrite\SDK\Language\Android;
use Appwrite\SDK\Language\Apple;
use Appwrite\SDK\Language\CLI;
use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\Deno;
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
use Appwrite\SDK\Language\Swift;
use Appwrite\SDK\Language\Unity;
use Appwrite\SDK\Language\Web;
use Appwrite\SDK\SDK;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Parser;

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
        'deno' => ['src/enums/webhook-event.ts', 'UserCreated', 'src/enums/localized-status.ts', 'Value1', 'src/enums/province-type.ts', 'Capital'],
        'php' => ['src/Appwrite/Enums/WebhookEvent.php', 'public const USERCREATED', 'src/Appwrite/Enums/LocalizedStatus.php', 'public static function VALUE1', 'src/Appwrite/Enums/ProvinceType.php', 'public static function CAPITAL'],
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
            'deno' => Deno::class,
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
        $language = new (self::languageClasses()[$name])();
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
    private function generate(string $name, string $platform): array
    {
        if (isset(self::$generated[$name][$platform])) {
            return self::$generated[$name][$platform];
        }

        $dir = self::OUTPUT . '/' . $name . '/' . $platform;
        $this->removeDirectory($dir);

        $sdk = new SDK($this->language($name), Parser::parse((string) \file_get_contents(self::FIXTURE)));
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

        return self::$generated[$name][$platform] = $files;
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
     * A canonical document keys `x-appwrite.auth` by platform. The fixture's
     * platform-auth operation lists `Project` for client and `Project, Key` for
     * server, and the server alias variant lists `Project, JWT`.
     */
    public function testExampleCredentialsFollowPlatformAuth(): void
    {
        $examples = [
            ['server', 'docs/examples/general/zzderivedauth.md', ['->setproject(', '->setkey('], ['->setjwt(', '->setsession(']],
            ['client', 'docs/examples/general/zzderivedauth.md', ['->setproject('], ['->setkey(', '->setjwt(', '->setsession(']],
            ['server', 'docs/examples/general/zzplatformalias.md', ['->setproject(', '->setjwt('], ['->setkey(', '->setsession(']],
            ['client', 'docs/examples/general/zzplatformalias.md', ['->setproject('], ['->setkey(', '->setjwt(', '->setsession(']],
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

    /** @return Iterator<string, array{string, string}> */
    public static function webLanguages(): Iterator
    {
        yield 'web client' => ['web', 'client'];
        yield 'web console' => ['web', 'console'];
        yield 'node server' => ['node', 'server'];
        yield 'react-native client' => ['react-native', 'client'];
    }

    #[DataProvider('webLanguages')]
    public function testEmptyRequestHeadersAreFormatterClean(string $name, string $platform): void
    {
        $files = $this->generate($name, $platform);
        $service = $files['src/services/general.ts'];
        $this->assertStringContainsString('zznoheaders(', $service);

        $emptyHeaders = $name === 'react-native'
            ? "return this.client.call('get', uri, {}, payload);"
            : 'const apiheaders: { [header: string]: string } = {};';
        $this->assertStringContainsString($emptyHeaders, $service);
        $this->assertDoesNotMatchRegularExpression('/\{\s+\}/', $service);

        // Populated header objects must retain their existing multiline layout.
        if ($platform !== 'console') {
            $this->assertStringContainsString("'x-appwrite-project': this.client.config.project,", $service);
        }
        $this->assertStringContainsString("accept: 'application/json',", $service);
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

    /**
     * Twig autoescapes to HTML, so a description that reaches a template through
     * an unsafe filter arrives as `&quot;` rather than `"`. Go doc comments are
     * read as plain text, so the entity is what the reader sees.
     */
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
            $this->assertSame('(WebhookEvent | (string & {}))', $language->getTypeName($openScalar, $specification));
            $this->assertSame('(WebhookEvent | (string & {}))[]', $language->getTypeName($openArray, $specification));

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
