<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Log;

class LogOptions
{
    public const LOG_DISABLED      = 0;
    public const LOG_REQUESTS_ONLY = 1;
    public const LOG_ALL           = 2;

    /**
     * @var array
     */
    protected static $options = [
        self::LOG_DISABLED      => 'Disabled',
        self::LOG_REQUESTS_ONLY => 'Requests only',
        self::LOG_ALL           => 'Both requests and responses',
    ];

    /**
     * @return array
     */
    public static function getOptions()
    {
        return self::$options;
    }
}
