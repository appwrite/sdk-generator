<?php

declare(strict_types=1);

namespace Appwrite\SDK\Extension;

/**
 * The keys of the `x-appwrite` extension the generator reads.
 */
enum Appwrite: string
{
    /** SDK platforms an operation, method alias or security scheme is available on. */
    case PLATFORMS = 'platforms';

    /** Method aliases generated from one operation. */
    case METHODS = 'methods';

    /** Whether an operation is part of a packaging flow. */
    case PACKAGING = 'packaging';

    /** On a security scheme: `path` when the scheme is supplied as a path parameter. */
    case LOCATION = 'location';

    /** On a path-bound security scheme: the path parameter it fills. */
    case PARAM = 'param';

    /** On a path-bound security scheme: the client config key that fills it. */
    case CONFIG = 'config';

    /** Schemes an example configures on the client, flat or keyed by SDK platform. */
    case AUTH = 'auth';
}
