<?php

declare(strict_types=1);

namespace Tests\E2E;

use JsonException;
use stdClass;

final class Json
{
    public static function same(string $expected, string $actual): bool
    {
        try {
            $expected = \json_decode($expected, flags: JSON_THROW_ON_ERROR);
            $actual = \json_decode($actual, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        return \json_encode(self::canonical($expected)) === \json_encode(self::canonical($actual));
    }

    private static function canonical(mixed $value): mixed
    {
        if (\is_array($value)) {
            return \array_map(self::canonical(...), $value);
        }

        if ($value instanceof stdClass) {
            $properties = \array_map(self::canonical(...), \get_object_vars($value));
            \ksort($properties);

            return (object) $properties;
        }

        return $value;
    }
}
