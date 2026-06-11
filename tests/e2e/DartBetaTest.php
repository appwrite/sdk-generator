<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Dart;

final class DartBetaTest extends Base
{
    #[Override]
    protected string $sdkName = 'dart';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'dart';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'dart';
    #[Override]
    protected string $class = Dart::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/dart/tests',
        'cp tests/e2e/languages/dart/tests.dart tests/e2e/sdks/dart/tests/tests.dart',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/dart dart:beta sh -c "dart pub get && dart pub run tests/tests.dart"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
