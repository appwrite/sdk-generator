<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Exception;

abstract class HTTP extends Language
{
    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    /**
     * @param array $parameter
     * @return string
     * @throws Exception
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        throw new Exception('Method not supported for HTTP APIs');
    }
}
