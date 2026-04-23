<?php

namespace Tests;

class Cpp20Test extends Base
{
    protected string $sdkName = "cpp";
    protected string $sdkPlatform = "server";
    protected string $sdkLanguage = "cpp";
    protected string $version = "0.0.1";

    protected string $language = "cpp";
    protected string $class = "Appwrite\SDK\Language\Cpp";
    protected array $build = [
        "mkdir -p tests/tmp/cpp",
        "cp -Rf tests/sdks/cpp/* tests/tmp/cpp/",
        "docker build -t appwrite-cpp-env tests/languages/cpp/"
    ];
    protected string $command = 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app appwrite-cpp-env sh -c "cd tests/languages/cpp/ && chmod +x ./test.sh && ./test.sh"';
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
    ];

    public function setUp(): void
    {
        parent::setUp();
        \array_shift($this->expectedOutput); // C++ doesn't print SDK header line

        // Wait for mock server to be ready
        $attempts = 0;
        $maxAttempts = 30;
        while ($attempts < $maxAttempts) {
            $output = [];
            $resultCode = 0;
            // Greptile fix: Query the actual container name (Compose adds prefixes like sdk-generator-mockapi-1)
            $names = [];
            \exec('docker ps --filter "name=mockapi" --format "{{.Names}}" 2>/dev/null', $names);
            $containerName = $names[0] ?? 'mockapi';
            \exec("docker exec {$containerName} curl -sf http://localhost/v1/health/version 2>/dev/null", $output, $resultCode);
            if ($resultCode === 0 && !empty($output)) {
                return;
            }
            $attempts++;
            sleep(1);
        }
        throw new \Exception("Timed out waiting for mock server via docker exec mockapi curl");
    }
}
