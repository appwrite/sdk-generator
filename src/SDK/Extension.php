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

    /** On a component schema: generated as a request model rather than a response model. */
    case REQUEST_MODEL = 'x-request-model';

    /** Generator annotation on a path parameter filled from a security scheme. */
    case SDK_SOURCE = 'x-sdk-source';

    /** Generator annotation naming the client config key that fills such a parameter. */
    case SDK_CONFIG = 'x-sdk-config';
}
