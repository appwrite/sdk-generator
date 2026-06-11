<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Kotlin;

final class KotlinJava17Test extends Base
{
    #[Override]
    protected string $sdkName = 'kotlin';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'kotlin';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'kotlin';
    #[Override]
    protected string $class = Kotlin::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/kotlin/src/test/kotlin',
        'cp tests/e2e/languages/kotlin/Tests.kt tests/e2e/sdks/kotlin/src/test/kotlin/Tests.kt',
        'chmod +x tests/e2e/sdks/kotlin/gradlew',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" -v $(pwd):/app -w /app/tests/e2e/sdks/kotlin eclipse-temurin:17-jdk-jammy sh -c "./gradlew test -q && cat result.txt"';

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
