<?php

namespace Appwrite\Spec;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getNamespace(): string
    {
        return $this->namespace !== '' ? $this->namespace : $this->title;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getEndpointDocs(): string
    {
        return $this->endpointDocs;
    }

    public function getLicenseName(): string
    {
        return $this->licenseName;
    }

    public function getLicenseURL(): string
    {
        return $this->licenseURL;
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function getContactURL(): string
    {
        return $this->contactURL;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }
}
