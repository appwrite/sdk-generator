<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once __DIR__ . '/../fixtures/utopia-openapi-contract/bootstrap.php';

use Appwrite\Spec\OpenAPI3;
use Appwrite\Spec\UtopiaAdapter;
use PHPUnit\Framework\TestCase;
use Utopia\OpenAPI\Model\Contact;
use Utopia\OpenAPI\Model\HttpMethod;
use Utopia\OpenAPI\Model\Info;
use Utopia\OpenAPI\Model\License;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\PathItem;
use Utopia\OpenAPI\Model\Server;
use Utopia\OpenAPI\Model\Tag;
use Utopia\OpenAPI\Specification;
use Utopia\OpenAPI\Version;

final class UtopiaAdapterTest extends TestCase
{
    public function testAdaptsCanonicalMetadata(): void
    {
        $adapter = new UtopiaAdapter($this->createSpecification());

        $this->assertSame('Example', $adapter->getTitle());
        $this->assertSame('Example API', $adapter->getDescription());
        $this->assertSame('Example', $adapter->getNamespace());
        $this->assertSame('1.0.0', $adapter->getVersion());
        $this->assertSame('https://{region}.example.com/v1', $adapter->getEndpoint());
        $this->assertSame('https://<REGION>.example.com/v1', $adapter->getEndpointDocs());
        $this->assertSame('MIT', $adapter->getLicenseName());
        $this->assertSame('https://example.com/license', $adapter->getLicenseURL());
        $this->assertSame('Utopia', $adapter->getContactName());
        $this->assertSame('https://example.com', $adapter->getContactURL());
        $this->assertSame('team@example.com', $adapter->getContactEmail());
    }

    public function testGroupsOperationsByOfficialTags(): void
    {
        $adapter = new UtopiaAdapter($this->createSpecification());
        $services = $adapter->getServices();

        $this->assertSame(['account'], \array_keys($services));
        $this->assertSame('Account API', $services['account']['description']);
        $this->assertCount(1, $services['account']['methods']);
    }

    public function testMapsOfficialOperationFieldsToLegacyContract(): void
    {
        $adapter = new UtopiaAdapter($this->createSpecification());
        $method = $adapter->getMethods('account')[0];

        $this->assertSame('get', $method['method']);
        $this->assertSame('/account', $method['path']);
        $this->assertSame('/v1/account', $method['fullPath']);
        $this->assertSame('get', $method['name']);
        $this->assertSame('Get account', $method['title']);
        $this->assertSame('Returns the current account.', $method['description']);
        $this->assertTrue($method['deprecated']);
    }

    public function testDelegatesUnmigratedContractToCompatibilityParser(): void
    {
        $legacy = $this->createMock(OpenAPI3::class);
        $legacy->expects($this->once())->method('getDefinitions')->willReturn([
            'account' => ['name' => 'account'],
        ]);
        $legacy->expects($this->once())->method('getMethods')->with('account')->willReturn([
            ['name' => 'legacyGet'],
        ]);

        $adapter = new UtopiaAdapter($this->createSpecification(), $legacy);

        $this->assertSame(['account' => ['name' => 'account']], $adapter->getDefinitions());
        $this->assertSame([['name' => 'legacyGet']], $adapter->getMethods('account'));
    }

    private function createSpecification(): Specification
    {
        $operation = new Operation(
            id: 'accountGet',
            method: HttpMethod::GET,
            path: '/account',
            tags: ['account'],
            summary: 'Get account',
            description: 'Returns the current account.',
            deprecated: true,
            parameters: [],
            requestBody: null,
            responses: [],
            security: [],
        );

        return new Specification(
            version: Version::V3_1,
            info: new Info(
                title: 'Example',
                description: 'Example API',
                version: '1.0.0',
                termsOfService: null,
                contact: new Contact(
                    name: 'Utopia',
                    url: 'https://example.com',
                    email: 'team@example.com',
                ),
                license: new License(
                    name: 'MIT',
                    url: 'https://example.com/license',
                ),
            ),
            servers: [new Server(url: 'https://{region}.example.com/v1')],
            tags: [
                'account' => new Tag(
                    name: 'account',
                    description: 'Account API',
                ),
            ],
            paths: [
                '/account' => new PathItem(
                    path: '/account',
                    operations: [HttpMethod::GET->value => $operation],
                    parameters: [],
                ),
            ],
            schemas: [],
            securitySchemes: [],
            security: [],
        );
    }
}
