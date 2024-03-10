<?php

namespace Tests;

class FlutterBetaTest extends Base
{
    protected string $sdkName = 'flutter';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'flutter';
    protected string $version = '0.0.1';

    protected string $language = 'flutter';
    protected string $class = 'Appwrite\SDK\Language\Flutter';
    protected array $build = [
        'mkdir -p tests/sdks/flutter/test',
        'cp tests/languages/flutter/tests.dart tests/sdks/flutter/test/appwrite_test.dart',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/flutter fischerscode/flutter-sudo:beta sh -c "sudo chown -R flutter:flutter . && flutter pub get && flutter test test/appwrite_test.dart"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}
