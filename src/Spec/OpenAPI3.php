<?php

namespace Appwrite\Spec;

class OpenAPI3 extends Swagger2
{
    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getAttribute('servers.0.url', 'https://example.com/v1');
    }
}