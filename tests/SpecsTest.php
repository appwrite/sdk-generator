<?php

use PHPUnit\Framework\TestCase;
use Utopia\Fetch\Client;

class SpecsTest extends TestCase
{
    public function testSpecs()
    {
            $specsPath = dirname(__FILE__) . '/resources/spec.json';
            $specs = json_decode(file_get_contents($specsPath), true);

            $client = new Client();
            $client->addHeader('content-type', 'application/json');

            $response = $client->fetch(
                url: 'https://validator.swagger.io/validator/debug',
                method: Client::METHOD_POST,
                body: $specs
            );

            $this->assertEquals(200, $response->getStatusCode(), 'Failed to validate specs: ' . $response->getBody());

            $body = $response->json();
            $this->assertEmpty($body['schemaValidationMessages'], 'Schema validation failed: ' . json_encode($body['schemaValidationMessages'], JSON_PRETTY_PRINT));
    }
}
