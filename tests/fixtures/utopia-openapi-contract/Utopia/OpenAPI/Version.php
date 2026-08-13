<?php

declare(strict_types=1);

namespace Utopia\OpenAPI;

enum Version: string
{
    case V2 = '2.0';
    case V3_0 = '3.0';
    case V3_1 = '3.1';
}
