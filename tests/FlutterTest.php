<?php

namespace Tests;

class FlutterTest extends Base
{
    protected string $language = 'flutter';
    protected string $class = 'Appwrite\SDK\Language\Flutter';
    protected array $build = [
        'mkdir -p tests/sdks/flutter/test',
        'cp tests/languages/flutter/tests.dart tests/sdks/flutter/test/appwrite_test.dart',
    ];
    protected array $envs = [
        'flutter-stable' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/flutter --env PUB_CACHE=vendor cirrusci/flutter:stable sh -c "flutter pub get && flutter test test/appwrite_test.dart"',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
    ];
}
