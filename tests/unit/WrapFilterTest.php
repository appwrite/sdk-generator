<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\SDK;
use Appwrite\Spec\OpenAPI3;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Twig\Environment;

/**
 * Tests for the generic `wrap(width, prefix)` Twig filter (SDK.php).
 *
 * The filter splits text into lines, word-wraps each line to `width`
 * columns and prefixes every resulting line with `prefix` — the shared
 * recipe that previously lived as duplicated per-language comment filters.
 */
final class WrapFilterTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $spec = new OpenAPI3(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json'));
        $sdk = new SDK(new Dart(), $spec);

        // The Twig environment (with all filters registered) is protected.
        $property = new ReflectionProperty(SDK::class, 'twig');
        $this->twig = $property->getValue($sdk);
    }

    private function wrap(string $value, ?int $width = null, ?string $prefix = null): string
    {
        $args = ['value' => $value];
        $call = '{{ value | wrap';
        if ($width !== null) {
            $call .= '(' . $width . ($prefix !== null ? ", '" . $prefix . "'" : '') . ')';
        }
        $call .= ' }}';

        return $this->twig->createTemplate($call)->render($args);
    }

    public function testShortLineIsPrefixedAndNotWrapped(): void
    {
        $this->assertSame('  /// Hello world', $this->wrap('Hello world', 75, '  /// '));
    }

    public function testEachInputLineIsPrefixed(): void
    {
        $this->assertSame(
            "// first\n// second",
            $this->wrap("first\nsecond", 75, '// ')
        );
    }

    public function testLongLineWrapsAtWidthWithPrefixOnContinuation(): void
    {
        $input = 'aaaa bbbb cccc dddd eeee'; // 24 chars, force a wrap at width 10
        $this->assertSame(
            "# aaaa bbbb\n# cccc dddd\n# eeee",
            $this->wrap($input, 10, '# ')
        );
    }

    public function testEmptyPrefixDefaultsToPlainWordwrap(): void
    {
        $this->assertSame("aaaa\nbbbb", $this->wrap('aaaa bbbb', 4));
    }

    public function testReproducesLegacyDartCommentBehavior(): void
    {
        // Mirrors the old dartComment filter: prefix '  /// ', width 75.
        $input = 'Get a list of all the user activity logs. You can use the query params to filter your results.';
        $expected = '';
        foreach (explode("\n", $input) as $i => $line) {
            $expected .= ($i > 0 ? "\n" : '') . '  /// ' . wordwrap($line, 75, "\n  /// ");
        }

        $this->assertSame($expected, $this->wrap($input, 75, '  /// '));
    }
}
