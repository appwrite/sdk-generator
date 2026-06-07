<?php

declare(strict_types=1);

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Exception;

abstract class HTTP extends Language
{
    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [];
    }

    public function getIdentifierOverrides(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        throw new Exception('Method not supported for HTTP APIs');
    }
}
