<?php

namespace Utopia\MockServer\Utopia;

use Utopia\Config\Config;

// Taken from app/config/errors.php
const errorConfig = [
    /** General Errors */
    Exception::GENERAL_UNKNOWN => [
        'name' => Exception::GENERAL_UNKNOWN,
        'description' => 'An unknown error has occured. Please check the logs for more information.',
        'code' => 500,
    ],
    Exception::GENERAL_MOCK => [
        'name' => Exception::GENERAL_MOCK,
        'description' => 'General errors thrown by the mock controller used for testing.',
        'code' => 400,
    ],
    Exception::GENERAL_ACCESS_FORBIDDEN => [
        'name' => Exception::GENERAL_ACCESS_FORBIDDEN,
        'description' => 'Access to this API is forbidden.',
        'code' => 401,
    ],
    Exception::GENERAL_UNKNOWN_ORIGIN => [
        'name' => Exception::GENERAL_UNKNOWN_ORIGIN,
        'description' => 'The request originated from an unknown origin. If you trust this domain, please list it as a trusted platform in the Appwrite console.',
        'code' => 403,
    ],
    Exception::GENERAL_SERVICE_DISABLED => [
        'name' => Exception::GENERAL_SERVICE_DISABLED,
        'description' => 'The requested service is disabled. You can enable the service from the Appwrite console.',
        'code' => 503,
    ],
    Exception::GENERAL_UNAUTHORIZED_SCOPE => [
        'name' => Exception::GENERAL_UNAUTHORIZED_SCOPE,
        'description' => 'The current user or API key does not have the required scopes to access the requested resource.',
        'code' => 401,
    ],
    Exception::GENERAL_RATE_LIMIT_EXCEEDED => [
        'name' => Exception::GENERAL_RATE_LIMIT_EXCEEDED,
        'description' => 'Rate limit for the current endpoint has been exceeded. Please try again after some time.',
        'code' => 429,
    ],
    Exception::GENERAL_SMTP_DISABLED => [
        'name' => Exception::GENERAL_SMTP_DISABLED,
        'description' => 'SMTP is disabled on your Appwrite instance. You can <a href="/docs/email-delivery">learn more about setting up SMTP</a> in our docs.',
        'code' => 503,
    ],
    Exception::GENERAL_PHONE_DISABLED => [
        'name' => Exception::GENERAL_PHONE_DISABLED,
        'description' => 'Phone provider is not configured. Please check the _APP_SMS_PROVIDER environment variable of your Appwrite server.',
        'code' => 503,
    ],
    Exception::GENERAL_ARGUMENT_INVALID => [
        'name' => Exception::GENERAL_ARGUMENT_INVALID,
        'description' => 'The request contains one or more invalid arguments. Please refer to the endpoint documentation.',
        'code' => 400,
    ],
    Exception::GENERAL_QUERY_LIMIT_EXCEEDED => [
        'name' => Exception::GENERAL_QUERY_LIMIT_EXCEEDED,
        'description' => 'Query limit exceeded for the current attribute. Usage of more than 100 query values on a single attribute is prohibited.',
        'code' => 400,
    ],
    Exception::GENERAL_QUERY_INVALID => [
        'name' => Exception::GENERAL_QUERY_INVALID,
        'description' => 'The query\'s syntax is invalid. Please check the query and try again.',
        'code' => 400,
    ],
    Exception::GENERAL_ROUTE_NOT_FOUND => [
        'name' => Exception::GENERAL_ROUTE_NOT_FOUND,
        'description' => 'Route not found. Please ensure the endpoint is configured correctly and that the API route is valid for this SDK version. Refer to the API docs for more details.',
        'code' => 404,
    ],
    Exception::GENERAL_CURSOR_NOT_FOUND => [
        'name' => Exception::GENERAL_CURSOR_NOT_FOUND,
        'description' => 'The cursor is invalid. This can happen if the item represented by the cursor has been deleted.',
        'code' => 400,
    ],
    Exception::GENERAL_SERVER_ERROR => [
        'name' => Exception::GENERAL_SERVER_ERROR,
        'description' => 'An internal server error occurred.',
        'code' => 500,
    ],
    Exception::GENERAL_PROTOCOL_UNSUPPORTED => [
        'name' => Exception::GENERAL_PROTOCOL_UNSUPPORTED,
        'description' => 'The request cannot be fulfilled with the current protocol. Please check the value of the _APP_OPTIONS_FORCE_HTTPS environment variable.',
        'code' => 500,
    ],
    Exception::GENERAL_CODES_DISABLED => [
        'name' => Exception::GENERAL_CODES_DISABLED,
        'description' => 'Invitation codes are disabled on this server. Please contact the server administrator.',
        'code' => 500,
    ],
    Exception::GENERAL_USAGE_DISABLED => [
        'name' => Exception::GENERAL_USAGE_DISABLED,
        'description' => 'Usage stats is not configured. Please check the value of the _APP_USAGE_STATS environment variable of your Appwrite server.',
        'code' => 501,
    ],
    Exception::GENERAL_NOT_IMPLEMENTED => [
        'name' => Exception::GENERAL_NOT_IMPLEMENTED,
        'description' => 'This method was not fully implemented yet. If you believe this is a mistake, please upgrade your Appwrite server version.',
        'code' => 405,
    ],

    /** Functions  */
    Exception::FUNCTION_NOT_FOUND => [
        'name' => Exception::FUNCTION_NOT_FOUND,
        'description' => 'Function with the requested ID could not be found.',
        'code' => 404,
    ],
];

class Exception extends \Exception
{
    /**
     * Error Codes
     *
     * Naming the error types based on the following convention
     * <ENTITY>_<ERROR_TYPE>
     *
     * Appwrite has the following entities:
     * - General
     */

    /** General */
    public const GENERAL_UNKNOWN                   = 'general_unknown';
    public const GENERAL_MOCK                      = 'general_mock';
    public const GENERAL_ACCESS_FORBIDDEN          = 'general_access_forbidden';
    public const GENERAL_UNKNOWN_ORIGIN            = 'general_unknown_origin';
    public const GENERAL_SERVICE_DISABLED          = 'general_service_disabled';
    public const GENERAL_UNAUTHORIZED_SCOPE        = 'general_unauthorized_scope';
    public const GENERAL_RATE_LIMIT_EXCEEDED       = 'general_rate_limit_exceeded';
    public const GENERAL_SMTP_DISABLED             = 'general_smtp_disabled';
    public const GENERAL_PHONE_DISABLED            = 'general_phone_disabled';
    public const GENERAL_ARGUMENT_INVALID          = 'general_argument_invalid';
    public const GENERAL_QUERY_LIMIT_EXCEEDED      = 'general_query_limit_exceeded';
    public const GENERAL_QUERY_INVALID             = 'general_query_invalid';
    public const GENERAL_ROUTE_NOT_FOUND           = 'general_route_not_found';
    public const GENERAL_CURSOR_NOT_FOUND          = 'general_cursor_not_found';
    public const GENERAL_SERVER_ERROR              = 'general_server_error';
    public const GENERAL_PROTOCOL_UNSUPPORTED      = 'general_protocol_unsupported';
    public const GENERAL_CODES_DISABLED            = 'general_codes_disabled';
    public const GENERAL_USAGE_DISABLED            = 'general_usage_disabled';
    public const GENERAL_NOT_IMPLEMENTED           = 'general_not_implemented';

    /** Functions */
    public const FUNCTION_NOT_FOUND                = 'function_not_found';

    protected $type = '';
    protected $errors = [];

    public function __construct(string $type = Exception::GENERAL_UNKNOWN, string $message = null, int $code = null, \Throwable $previous = null)
    {
        $this->errors = errorConfig;
        $this->type = $type;

        if (isset($this->errors[$type])) {
            $this->code = $this->errors[$type]['code'];
            $this->message = $this->errors[$type]['description'];
        }

        $this->message = $message ?? $this->message;
        $this->code = $code ?? $this->code;

        parent::__construct($this->message, $this->code, $previous);
    }

    /**
     * Get the type of the exception.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the type of the exception.
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
