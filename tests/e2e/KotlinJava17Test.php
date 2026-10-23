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
        'mkdir -p tests/e2e/sdks/kotlin/src/test/java/io/appwrite',
        'cp tests/e2e/languages/kotlin/JavaInterop.java tests/e2e/sdks/kotlin/src/test/java/io/appwrite/JavaInterop.java',
        'php tests/e2e/languages/kotlin/java-examples.php tests/e2e/sdks/kotlin/docs/examples/java tests/e2e/sdks/kotlin/src/test/java/examples',
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
        ...Base::SERVER_REDIRECT_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::PATH_PARAM_RESPONSES,
        ...Base::PATH_SEGMENT_RESPONSES,
        ...Base::TEXT_RESPONSES,
        ...Base::TEXT_UPLOAD_RESPONSES,
        ...Base::OPTIONAL_ATTACHMENT_RESPONSES,
        ...Base::MULTIPART_OBJECT_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::BOUNDARY_UPLOAD_RESPONSES,
        ...Base::GENERIC_UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::MODEL_DEFAULT_RESPONSES,
        ...Base::NULLABLE_MODEL_RESPONSES,
        ...Base::ENUM_ARRAY_MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        Base::QUERY_TRANSPORT_RESPONSE,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::JAVA_INTEROP_RESPONSES,
        ...Base::TOPIC_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
