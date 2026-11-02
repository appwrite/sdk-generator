<?php

declare(strict_types=1);

namespace Tests\E2E;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    #[DataProvider('pairs')]
    public function testSame(string $expected, string $actual, bool $same): void
    {
        $this->assertSame($same, Json::same($expected, $actual));
    }

    /**
     * @return Iterator<string, array{string, string, bool}>
     */
    public static function pairs(): Iterator
    {
        yield 'key order' => ['{"method":"limit","values":[1]}', '{"values":[1],"method":"limit"}', true];
        yield 'nested key order' => ['{"a":{"b":1,"c":2}}', '{"a":{"c":2,"b":1}}', true];
        yield 'list order' => ['{"values":[1,2]}', '{"values":[2,1]}', false];
        yield 'empty object for empty list' => ['{"method":"arrayUnique","values":[]}', '{"method":"arrayUnique","values":{}}', false];
        yield 'empty list for empty object' => ['{"settings":{}}', '{"settings":[]}', false];
        yield 'string for number' => ['{"values":[1]}', '{"values":["1"]}', false];
        yield 'same number written as a float' => ['{"values":[1]}', '{"values":[1.0]}', true];
        yield 'missing key' => ['{"method":"limit","values":[1]}', '{"method":"limit"}', false];
        yield 'not json' => ['{"method":"limit"}', 'method=limit', false];
    }
}
