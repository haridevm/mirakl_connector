<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Shipping\Rates;

class Error
{
    public const INTERNAL_SERVER_ERROR                    = 0;
    public const HTTP_STATUS_CODE_EXCEPTION               = 1;
    public const HTTP_MESSAGE_NOT_READABLE_EXCEPTION      = 2;
    public const TENANT_NOT_FOUND_EXCEPTION               = 3;
    public const PROVIDER_CONFIGURATION_SERVICE_EXCEPTION = 4;
    public const PROVIDER_EXECUTION_SERVICE_EXCEPTION     = 5;
    public const VALIDATION_SERVICE_EXCEPTION             = 6;
    public const SERVICE_EXCEPTION                        = 7;
    public const REQUEST_TIMEOUT_EXCEPTION                = 8;
    public const ILLEGAL_ARGUMENT_SERVICE_EXCEPTION       = 9;

    /**
     * @param int $code
     * @return bool
     */
    public static function isProviderError($code)
    {
        return $code == self::PROVIDER_CONFIGURATION_SERVICE_EXCEPTION
            || $code == self::PROVIDER_EXECUTION_SERVICE_EXCEPTION;
    }
}
