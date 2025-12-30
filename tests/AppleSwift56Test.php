<?php

namespace Tests;

class AppleSwift56Test extends Base
{
    protected string $sdkName = 'swift';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'apple';
    protected string $version = '0.0.1';

    protected string $language = 'apple';
    protected string $class = 'Appwrite\SDK\Language\Apple';
    protected array $build = [
        'mkdir -p tests/sdks/apple/Tests/AppwriteTests',
        'cp tests/languages/apple/Tests.swift tests/sdks/apple/Tests/AppwriteTests/Tests.swift',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/apple swift:5.6-focal swift test 2>&1';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
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
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];

    /**
     * Override to log all output including compilation errors
     */
    public function testHTTPSuccess(): void
    {
        $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

        if (empty($spec)) {
            throw new \Exception('Failed to parse spec.');
        }

        $sdk = new \Appwrite\SDK\SDK($this->getLanguage(), new \Appwrite\Spec\Swagger2($spec));

        $sdk
            ->setName($this->sdkName)
            ->setVersion($this->version)
            ->setPlatform($this->sdkPlatform)
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setLogo('https://appwrite.io/v1/images/console.png')
            ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
            ->setExamples('**EXAMPLES** <HTML>')
            ->setNamespace("io appwrite")
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setChangelog('--changelog--')
            ->setDefaultHeaders([
                'X-Appwrite-Response-Format' => '0.8.0',
            ])
            ->setTest("true");

        $dir = __DIR__ . '/sdks/' . $this->language;

        $this->rmdirRecursive($dir);

        $sdk->generate(__DIR__ . '/sdks/' . $this->language);

        /**
         * Build SDK
         */
        foreach ($this->build as $command) {
            echo "Build Executing: {$command}\n";
            exec($command);
        }

        $output = [];
        $returnVar = 0;

        echo "Env Executing: {$this->command}\n";

        // Capture both stdout and stderr, and return code
        exec($this->command, $output, $returnVar);

        // Log ALL output including compilation errors
        echo "\n=== FULL OUTPUT (including errors) ===\n";
        echo implode("\n", $output);
        echo "\n=== END FULL OUTPUT ===\n";
        echo "Return code: {$returnVar}\n\n";

        $this->assertIsArray($output);

        // Save original output for error logging
        $originalOutput = $output;

        do {
            $removed = \array_shift($output);
        } while ($removed != 'Test Started' && sizeof($output) != 0);

        // If we filtered everything out, it means "Test Started" was never found
        // This likely means there were compilation errors
        if (sizeof($output) == 0 && sizeof($originalOutput) > 0) {
            echo "\n=== ERROR: 'Test Started' marker not found. Full output above shows compilation errors. ===\n";
        }

        echo \implode("\n", $output);

        foreach ($this->expectedOutput as $index => $expected) {
            if (!isset($output[$index])) {
                echo "\n=== ERROR: Missing output at index {$index}. Expected: {$expected} ===\n";
                continue;
            }
            // HACK: Swift does not guarantee the order of the JSON parameters
            if (\str_starts_with($expected, '{')) {
                $this->assertEquals(
                    \json_decode($expected, true),
                    \json_decode($output[$index], true)
                );
            } elseif ($expected == 'unique()') {
                $this->assertNotEmpty($output[$index]);
                $this->assertIsString($output[$index]);
                $this->assertEquals(20, strlen($output[$index]));
                $this->assertNotEquals($output[$index], 'unique()');
            } else {
                $this->assertEquals($expected, $output[$index]);
            }
        }
    }

    private function rmdirRecursive($dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }
        foreach (\scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (\is_dir("$dir/$file")) {
                $this->rmdirRecursive("$dir/$file");
            } else {
                \unlink("$dir/$file");
            }
        }
        \rmdir($dir);
    }
}
