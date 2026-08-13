<?php

declare(strict_types=1);

namespace Appwrite\Spec;

use Override;
use Utopia\OpenAPI\Model\HttpMethod;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Specification;

/**
 * Adapts the canonical Utopia OpenAPI model to SDK Generator's existing Spec
 * contract. Compatibility policy stays here while templates migrate to the
 * canonical model.
 */
final class UtopiaAdapter extends Spec
{
    public function __construct(
        private readonly Specification $specification,
        private readonly ?Spec $compatibility = null,
    ) {
        parent::__construct('{}');
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->specification->info->title;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->specification->info->description;
    }

    #[Override]
    public function getNamespace(): string
    {
        return $this->compatibility?->getNamespace() ?? $this->getTitle();
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->specification->info->version;
    }

    #[Override]
    public function getEndpoint(): string
    {
        return $this->specification->servers[0]->url ?? 'https://example.com';
    }

    #[Override]
    public function getEndpointDocs(): string
    {
        if ($this->compatibility instanceof Spec) {
            return $this->compatibility->getEndpointDocs();
        }

        $endpoint = $this->getEndpoint();

        return \preg_replace_callback(
            '/\{([^}]+)\}/',
            fn(array $matches): string => '<' . \strtoupper($matches[1]) . '>',
            $endpoint,
        ) ?? '';
    }

    #[Override]
    public function getLicenseName(): string
    {
        return $this->specification->info->license?->name ?? '';
    }

    #[Override]
    public function getLicenseURL(): string
    {
        return $this->specification->info->license?->url ?? '';
    }

    #[Override]
    public function getContactName(): string
    {
        return $this->specification->info->contact?->name ?? '';
    }

    #[Override]
    public function getContactURL(): string
    {
        return $this->specification->info->contact?->url ?? '';
    }

    #[Override]
    public function getContactEmail(): string
    {
        return $this->specification->info->contact?->email ?? '';
    }

    #[Override]
    public function getServices(): array
    {
        if ($this->compatibility instanceof Spec) {
            $services = [];
            $legacy = $this->compatibility->getServices();

            foreach ($this->getOperations() as $operation) {
                foreach ($operation->tags as $tag) {
                    if (!isset($legacy[$tag])) {
                        continue;
                    }

                    $services[$tag] = $legacy[$tag];
                }
            }

            return $services;
        }

        $services = [];

        foreach ($this->getOperations() as $operation) {
            foreach ($operation->tags as $tag) {
                $services[$tag] ??= [
                    'name' => $tag,
                    'description' => $this->specification->tags[$tag]->description ?? '',
                    'methods' => [],
                ];
            }
        }

        foreach (\array_keys($services) as $name) {
            $services[$name]['methods'] = $this->getMethods($name);
        }

        return $services;
    }

    #[Override]
    public function getMethods($service): array
    {
        if ($this->compatibility instanceof Spec) {
            return $this->compatibility->getMethods($service);
        }

        $methods = [];

        foreach ($this->getOperations() as $operation) {
            if (!\in_array($service, $operation->tags, true)) {
                continue;
            }

            $methods[] = $this->mapOperation($operation, (string) $service);
        }

        return $methods;
    }

    #[Override]
    public function getGlobalHeaders(): array
    {
        return $this->compatibility?->getGlobalHeaders() ?? [];
    }

    #[Override]
    public function getDefinitions(): array
    {
        return $this->compatibility?->getDefinitions() ?? [];
    }

    #[Override]
    public function getRequestModels(): array
    {
        return $this->compatibility?->getRequestModels() ?? [];
    }

    #[Override]
    public function getRequestEnums(): array
    {
        return $this->compatibility?->getRequestEnums() ?? [];
    }

    #[Override]
    public function getResponseEnums(): array
    {
        return $this->compatibility?->getResponseEnums() ?? [];
    }

    #[Override]
    public function getRequestModelEnums(): array
    {
        return $this->compatibility?->getRequestModelEnums() ?? [];
    }

    #[Override]
    public function getAllEnums(): array
    {
        return $this->compatibility?->getAllEnums() ?? [];
    }

    /** @return list<Operation> */
    private function getOperations(): array
    {
        $operations = [];

        foreach ($this->specification->paths as $path) {
            foreach ($path->operations as $operation) {
                $operations[] = $operation;
            }
        }

        return $operations;
    }

    /** @return array<string, mixed> */
    private function mapOperation(Operation $operation, string $service): array
    {
        return [
            'method' => $operation->method instanceof HttpMethod
                ? $operation->method->value
                : (string) $operation->method,
            'path' => $operation->path,
            'fullPath' => (\parse_url($this->getEndpoint(), PHP_URL_PATH) ?: '') . $operation->path,
            'name' => $this->getMethodName($operation, $service),
            'packaging' => false,
            'title' => $operation->summary,
            'description' => $operation->description,
            'auth' => [],
            'security' => [],
            'securityHeaders' => [],
            'securityQueries' => [],
            'securityPathParams' => [],
            'consumes' => [],
            'produces' => [],
            'cookies' => false,
            'platforms' => [],
            'consoleOnly' => false,
            'type' => false,
            'deprecated' => $operation->deprecated,
            'headers' => [],
            'parameters' => [
                'all' => [],
                'header' => [],
                'path' => [],
                'query' => [],
                'body' => [],
            ],
            'emptyResponse' => true,
            'responseModel' => '',
            'responseModels' => [],
            'responseDiscriminator' => [],
        ];
    }

    private function getMethodName(Operation $operation, string $service): string
    {
        if (!\str_starts_with(\strtolower($operation->id), \strtolower($service))) {
            return $operation->id;
        }

        $name = \substr($operation->id, \strlen($service));

        return $name === '' ? $operation->id : \lcfirst($name);
    }
}
