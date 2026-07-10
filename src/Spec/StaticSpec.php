<?php

declare(strict_types=1);

namespace Appwrite\Spec;

use Override;
use ArrayObject;

class StaticSpec extends Spec
{
    public function __construct(
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly string $version = '',
        public readonly string $namespace = '',
        public readonly string $endpoint = '',
        public readonly string $endpointDocs = '',
        public readonly string $licenseName = '',
        public readonly string $licenseURL = '',
        public readonly string $contactName = '',
        public readonly string $contactURL = '',
        public readonly string $contactEmail = '',
    ) {
        ArrayObject::__construct([]);
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function getNamespace(): string
    {
        return $this->namespace !== '' ? $this->namespace : $this->title;
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->version;
    }

    #[Override]
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    #[Override]
    public function getEndpointDocs(): string
    {
        return $this->endpointDocs;
    }

    #[Override]
    public function getLicenseName(): string
    {
        return $this->licenseName;
    }

    #[Override]
    public function getLicenseURL(): string
    {
        return $this->licenseURL;
    }

    #[Override]
    public function getContactName(): string
    {
        return $this->contactName;
    }

    #[Override]
    public function getContactURL(): string
    {
        return $this->contactURL;
    }

    #[Override]
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }
}
