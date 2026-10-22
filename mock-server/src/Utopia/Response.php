<?php

namespace Utopia\MockServer\Utopia;

use Utopia\Http\Response as HttpResponse;

abstract class Response extends HttpResponse
{
    public const string MODEL_NONE = 'none';
    public const string MODEL_ANY = 'any';
    public const string MODEL_ERROR = 'error';
    public const string MODEL_MOCK = 'mock';
}
