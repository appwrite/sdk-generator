<?php

namespace Tests;

use Appwrite\SDK\Language\REST;
use PHPUnit\Framework\TestCase;

class RESTExamplesTest extends TestCase
{
    public function testCreateMembershipExampleUsesStableBodyParameters(): void
    {
        $language = new REST();
        $service = ['name' => 'teams'];
        $method = [
            'name' => 'createMembership',
            'parameters' => [
                'body' => [
                    ['name' => 'email'],
                    ['name' => 'userId'],
                    ['name' => 'phone'],
                    ['name' => 'roles'],
                    ['name' => 'url'],
                    ['name' => 'name'],
                ],
            ],
        ];

        $parameters = $language->getExampleBodyParameters($service, $method);

        $this->assertSame(['email', 'roles', 'url'], \array_column($parameters, 'name'));
    }

    public function testOtherMethodsKeepTheirOriginalBodyParameters(): void
    {
        $language = new REST();
        $service = ['name' => 'teams'];
        $method = [
            'name' => 'updateMembership',
            'parameters' => [
                'body' => [
                    ['name' => 'roles'],
                    ['name' => 'name'],
                ],
            ],
        ];

        $parameters = $language->getExampleBodyParameters($service, $method);

        $this->assertSame(['roles', 'name'], \array_column($parameters, 'name'));
    }
}
