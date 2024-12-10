<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication;

use Mirakl\MMP\Front\Client\FrontApiClientFactory;

class TestApiKey implements TestApiKeyInterface
{
    /**
     * @var FrontApiClientFactory
     */
    private $frontApiClientFactory;

    /**
     * @param FrontApiClientFactory $frontApiClientFactory
     */
    public function __construct(FrontApiClientFactory $frontApiClientFactory)
    {
        $this->frontApiClientFactory = $frontApiClientFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $apiUrl, string $apiKey): bool
    {
        if (empty($apiUrl) || empty($apiKey)) {
            return false;
        }

        $client = $this->frontApiClientFactory->create([
            'baseUrl' => $apiUrl,
            'apiKey'  => $apiKey,
        ]);

        try {
            $client->getVersion(); // Will throw exception is something is wrong with API key
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
