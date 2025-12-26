<?php

namespace Tests;

use Appwrite\SDK\Language\Web;
use PHPUnit\Framework\TestCase;

class WebUnionTypesTest extends TestCase
{
    private function createSpec(array $models): array
    {
        $definitions = [];
        foreach ($models as $model) {
            $definitions[$model] = [
                'additionalProperties' => false,
                'properties' => [],
            ];
        }

        return ['definitions' => $definitions];
    }

    private function createMethod(array $responseModels): array
    {
        return [
            'type' => '',
            'responseModel' => $responseModels[0] ?? '',
            'responseModels' => $responseModels,
        ];
    }

    public function testUnionTypesDisabledByDefault(): void
    {
        $web = new Web();
        $method = $this->createMethod(['mock', 'stub']);
        $spec = $this->createSpec(['mock', 'stub']);

        $returnType = $web->getReturn($method, $spec);

        // should return only first model (backward compatible)
        $this->assertEquals('Promise<Models.Mock>', $returnType);
    }

    public function testUnionTypesEnabled(): void
    {
        $web = new Web();
        $web->setEnableUnionTypes(true);

        $method = $this->createMethod(['mock', 'stub']);
        $spec = $this->createSpec(['mock', 'stub']);

        $returnType = $web->getReturn($method, $spec);

        // should return union type
        $this->assertEquals('Promise<Models.Mock | Models.Stub>', $returnType);
    }

    public function testSingleModelStillWorks(): void
    {
        $web = new Web();
        $web->setEnableUnionTypes(true);

        $method = $this->createMethod(['mock']);
        $spec = $this->createSpec(['mock']);

        $returnType = $web->getReturn($method, $spec);

        // single model should work normally
        $this->assertEquals('Promise<Models.Mock>', $returnType);
    }
}
