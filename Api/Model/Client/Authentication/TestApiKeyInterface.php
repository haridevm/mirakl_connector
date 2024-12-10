<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication;

interface TestApiKeyInterface
{
    /**
     * @param string $apiUrl
     * @param string $apiKey
     * @return bool
     */
    public function execute(string $apiUrl, string $apiKey): bool;
}
