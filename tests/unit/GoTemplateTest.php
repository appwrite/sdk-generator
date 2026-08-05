<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\SDK\Language\Go;
use Appwrite\SDK\SDK;
use Appwrite\Spec\OpenAPI3;
use PHPUnit\Framework\TestCase;

/**
 * Asserts on the Go SDK's generated request building.
 *
 * The e2e suite covers what the mock server can observe. A parameter sent in the
 * wrong place still reaches a route that ignores it, so these read the generated
 * source instead.
 */
final class GoTemplateTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = \sys_get_temp_dir() . '/sdk-generator-go-' . \bin2hex(\random_bytes(6));

        $spec = \file_get_contents(__DIR__ . '/../resources/spec-openapi3.json');
        $sdk = new SDK(new Go(), new OpenAPI3($spec));
        $sdk->setName('go')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setDescription('description')
            ->setShortDescription('short description')
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->generate($this->directory);
    }

    protected function tearDown(): void
    {
        if (!\is_dir($this->directory)) {
            return;
        }

        $command = \sprintf('rm -rf %s', \escapeshellarg($this->directory));
        \exec($command);
    }

    private function generated(string $path): string
    {
        $file = $this->directory . '/' . $path;
        $this->assertFileExists($file);

        return \file_get_contents($file);
    }

    public function testPathParametersAreEncoded(): void
    {
        $general = $this->generated('general/general.go');

        $this->assertStringContainsString(
            'strings.NewReplacer("{pathId}", client.EncodePath(PathId))',
            $general
        );
    }

    public function testEncodePathIsGenerated(): void
    {
        $client = $this->generated('client/client.go');

        $this->assertStringContainsString('func EncodePath(value string) string', $client);
    }

    /**
     * A path parameter is already substituted into the URL. Sending it again put
     * it in the query string of a GET and in the body of a PATCH.
     */
    public function testPathParametersAreNotAlsoSentAsParameters(): void
    {
        $general = $this->generated('general/general.go');

        $method = \strstr($general, 'func (srv *General) GetPath(');
        $method = \substr($method, 0, \strpos($method, "\n}\n"));

        $this->assertStringContainsString('params := map[string]interface{}{}', $method);
        $this->assertStringNotContainsString('params["pathId"]', $method);
    }

    public function testArrayQueryParametersAreIndexed(): void
    {
        $client = $this->generated('client/client.go');

        $this->assertStringContainsString('fmt.Sprintf("%s[%d]", key, i)', $client);
        $this->assertStringNotContainsString('fmt.Sprintf("%s[]", key)', $client);
    }

    public function testBodyParametersAreStillSent(): void
    {
        $general = $this->generated('general/general.go');

        $method = \strstr($general, 'func (srv *General) Upload(');
        $method = \substr($method, 0, \strpos($method, "\n}\n"));

        $this->assertStringContainsString('params["x"]', $method);
        $this->assertStringContainsString('params["y"]', $method);
    }
}
