<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client;

use Mirakl\Api\Model\Client\Authentication\Method\MethodInterface;

interface ClientSettingsInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getApiUrl(): string;

    /**
     * @return MethodInterface
     */
    public function getAuthMethod(): MethodInterface;

    /**
     * @return int
     */
    public function getConnectTimeout(): int;
}
