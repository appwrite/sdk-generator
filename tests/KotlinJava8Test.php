<?php

namespace Tests;

class KotlinJava8Test extends Base
{
    protected string $language = 'kotlin';
    protected string $class = 'Appwrite\SDK\Language\Kotlin';
    protected array $build = [
        'mkdir -p tests/sdks/kotlin/src/test/kotlin',
        'cp tests/languages/kotlin/ServiceTest.kt tests/sdks/kotlin/src/test/kotlin/ServiceTest.kt',
        'chmod +x tests/sdks/kotlin/gradlew',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/kotlin openjdk:8-jdk-slim sh -c "./gradlew test -q && cat result.txt"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES
    ];
}
