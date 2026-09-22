<?php

declare(strict_types=1);

namespace Appwrite\SDK;

/**
 * The vendor extensions the generator reads from an OpenAPI document.
 */
enum Extension: string
{
    /** Appwrite metadata on operations and security schemes; its keys are the {@see Extension\Appwrite} cases. */
    case APPWRITE = 'x-appwrite';
}
