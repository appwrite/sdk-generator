<?php

namespace Tests;

class JavaJava17Test extends Base
{
    protected string $sdkName = 'java';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'java';
    protected string $version = '0.0.1';

    protected string $language = 'java';
    protected string $class = 'Appwrite\SDK\Language\Java';
    protected array $build = [
        'mkdir -p tests/sdks/java/src/main/java/io/appwrite',
        'cp tests/languages/java/Tests.java tests/sdks/java/src/main/java/io/appwrite/Tests.java',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/java maven:3.9-eclipse-temurin-17 sh -c "mvn compile exec:java -Dexec.mainClass=io.appwrite.Tests -q 2>/dev/null"';

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
        ...Base::OPERATOR_HELPER_RESPONSES,
    ];

    public function getLanguage(): \Appwrite\SDK\Language
    {
        return new \Appwrite\SDK\Language\Java();
    }
}
