<?php

namespace Tests;

class DartTest extends Base
{
    protected string $language = 'dart';
    protected string $class = 'Appwrite\SDK\Language\Dart';
    protected array $build = [
        'mkdir -p tests/sdks/dart/tests',
        'cp tests/languages/dart/tests.dart tests/sdks/dart/tests/tests.dart',
    ];
    protected array $envs = [
        'dart-stable' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:stable sh -c "dart pub get && dart pub run tests/tests.dart"',
        'dart-beta' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:beta sh -c "dart pub get && dart pub run tests/tests.dart"',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}
