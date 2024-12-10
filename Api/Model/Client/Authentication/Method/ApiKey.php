<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;

class ApiKey implements MethodInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TestApiKeyInterface
     */
    private $testApiKey;

    /**
     * @param Config              $config
     * @param TestApiKeyInterface $testApiKey
     */
    public function __construct(Config $config, TestApiKeyInterface $testApiKey)
    {
        $this->config = $config;
        $this->testApiKey = $testApiKey;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return __('API Front Key')->render();
    }

    /**
     * @inheritdoc
     */
    public function getAuthHeader(): ?string
    {
        return $this->config->getApiKey() ?: null;
    }

    /**
     * @inheritdoc
     */
    public function testConnection(array $params): bool
    {
        $apiUrl = $params['api_url'] ?? '';
        $apiKey = $params['api_key'] ?? '';

        return $this->testApiKey->execute($apiUrl, $apiKey);
    }

    /**
     * @inheritdoc
     */
    public function validate(): void
    {
        if (!$this->getAuthHeader()) {
            throw new LocalizedException(__('Please provide the API Key.'));
        }
    }
}
