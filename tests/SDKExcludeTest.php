<?php

namespace Tests;

use Appwrite\SDK\Language;
use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

final class SDKExcludeTest extends TestCase
{
    public function testServiceScopedMethodExclusionsAccumulatePerMethodName(): void
    {
        $sdk = $this->createSdk();

        $sdk->setExclude([
            'methods' => [
                ['name' => 'updateLabels', 'service' => 'project'],
                ['name' => 'updateLabels', 'service' => 'users'],
            ],
        ]);

        $this->assertTrue($sdk->isMethodExcludedPublic(['name' => 'updateLabels'], 'project'));
        $this->assertTrue($sdk->isMethodExcludedPublic(['name' => 'updateLabels'], 'users'));
        $this->assertFalse($sdk->isMethodExcludedPublic(['name' => 'updateLabels'], 'teams'));
    }

    public function testGlobalMethodExclusionIsNotOverwrittenByServiceScopedRule(): void
    {
        $sdk = $this->createSdk();

        $sdk->setExclude([
            'methods' => [
                ['name' => 'updateLabels'],
                ['name' => 'updateLabels', 'service' => 'project'],
            ],
        ]);

        $this->assertTrue($sdk->isMethodExcludedPublic(['name' => 'updateLabels'], 'project'));
        $this->assertTrue($sdk->isMethodExcludedPublic(['name' => 'updateLabels'], 'users'));
    }

    private function createSdk(): SDKExcludeTestSDK
    {
        return new SDKExcludeTestSDK(
            new class extends Language {
                public function getName(): string
                {
                    return 'test';
                }

                public function getKeywords(): array
                {
                    return [];
                }

                public function getIdentifierOverrides(): array
                {
                    return [];
                }

                public function getStaticAccessOperator(): string
                {
                    return '::';
                }

                public function getStringQuote(): string
                {
                    return "'";
                }

                public function getArrayOf(string $elements): string
                {
                    return '[' . $elements . ']';
                }

                public function getFiles(): array
                {
                    return [];
                }

                public function getTypeName(array $parameter, array $spec = []): string
                {
                    return 'mixed';
                }

                public function getParamDefault(array $param): string
                {
                    return 'null';
                }

                public function getParamExample(array $param, string $lang = ''): string
                {
                    return 'null';
                }
            },
            new Swagger2('{"info":{"title":"Test"},"paths":{},"tags":[],"definitions":{}}')
        );
    }
}

final class SDKExcludeTestSDK extends SDK
{
    public function isMethodExcludedPublic(array $method, string $serviceName = ''): bool
    {
        return $this->isMethodExcluded($method, $serviceName);
    }
}
