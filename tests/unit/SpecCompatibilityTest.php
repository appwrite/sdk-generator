<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\Spec\OpenAPI3;
use Appwrite\Spec\Spec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Locks the internal data contract consumed by SDK templates while the OpenAPI
 * reader is migrated section by section. Update a checksum only when the
 * generated SDK contract is intentionally changed.
 */
final class SpecCompatibilityTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function sections(): iterable
    {
        yield 'metadata' => ['metadata', 'b803aa7490ab743b2ecfeaf6fa4bd4b9ca1b59c28f3a51b5ddbf7f4cbe38e971'];
        yield 'global headers' => ['globalHeaders', '23ef0093268009ffbe39d51f8c84ede4be0c9f2c8f4f2bbb2822b9d2d6a135bd'];
        yield 'services and methods' => ['services', '1202fe251708541957e60fce68a627f839b03a31c8675bc6e9b390fa6d239f1a'];
        yield 'definitions' => ['definitions', '738ca45812d551c53f087eb2fb2109d24e5d84043860d96559c2864a30aa27bb'];
        yield 'request models' => ['requestModels', '6a29bef58c3ca072f3084ab0e48dfca31252f0c6d673a5cb36c1e432a86ee0cb'];
        yield 'request enums' => ['requestEnums', 'bac17d969659d6a179d06cccc7f3d0455e5e32db44f33910a123faa4e7797b39'];
        yield 'response enums' => ['responseEnums', 'ce8d286f76b79fc46e5b112b32785dc5afbd7b1269a2efe270d4444f4aaf3ba2'];
        yield 'request model enums' => ['requestModelEnums', '4f53cda18c2baa0c0354bb5f9a3ecbe5ed12ab4d8e11ba873c2f11161202b945'];
        yield 'all enums' => ['allEnums', '965f6a8fba9b38aa3cb60f5c891789f816966130fa4fa350590ec1aa677a9750'];
    }

    #[DataProvider('sections')]
    public function testInternalGeneratorContractRemainsCompatible(string $section, string $checksum): void
    {
        $spec = new OpenAPI3(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json'));
        $contract = $this->dumpContract($spec);

        $this->assertSame(
            $checksum,
            \hash('sha256', \json_encode($contract[$section], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)),
        );
    }

    /** @return array<string, mixed> */
    private function dumpContract(Spec $spec): array
    {
        $services = [];
        foreach ($spec->getServices() as $name => $service) {
            $service['methods'] = $spec->getMethods($name);
            $services[$name] = $service;
        }

        return [
            'metadata' => [
                'title' => $spec->getTitle(),
                'description' => $spec->getDescription(),
                'namespace' => $spec->getNamespace(),
                'version' => $spec->getVersion(),
                'endpoint' => $spec->getEndpoint(),
                'endpointDocs' => $spec->getEndpointDocs(),
                'licenseName' => $spec->getLicenseName(),
                'licenseURL' => $spec->getLicenseURL(),
                'contactName' => $spec->getContactName(),
                'contactURL' => $spec->getContactURL(),
                'contactEmail' => $spec->getContactEmail(),
            ],
            'globalHeaders' => $spec->getGlobalHeaders(),
            'services' => $services,
            'definitions' => $spec->getDefinitions(),
            'requestModels' => $spec->getRequestModels(),
            'requestEnums' => $spec->getRequestEnums(),
            'responseEnums' => $spec->getResponseEnums(),
            'requestModelEnums' => $spec->getRequestModelEnums(),
            'allEnums' => $spec->getAllEnums(),
        ];
    }
}
