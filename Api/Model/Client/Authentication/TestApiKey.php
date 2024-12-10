<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication;

use Mirakl\MMP\Front\Client\FrontApiClient;

class TestApiKey implements TestApiKeyInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $apiUrl, string $apiKey): bool
    {
        if (empty($apiUrl) || empty($apiKey)) {
            return false;
        }

        $client = new FrontApiClient($apiUrl, $apiKey);

        try {
            $client->getVersion(); // Will throw exception is something is wrong with API key
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}