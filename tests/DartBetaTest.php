<?php

namespace Tests;

class DartBetaTest extends Base
{
    protected string $language = 'dart';
    protected string $class = 'Appwrite\SDK\Language\Dart';
    protected array $build = [
        'mkdir -p tests/sdks/dart/tests',
        'cp tests/languages/dart/tests.dart tests/sdks/dart/tests/tests.dart',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:beta sh -c "dart pub get && dart pub run tests/tests.dart"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...BASE::PERMISSIONS_HELPER_RESPONSES,
    ];
}
