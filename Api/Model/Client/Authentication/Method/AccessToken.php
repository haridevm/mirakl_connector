<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Decorator\DecoratorInterface;

class AccessToken implements MethodInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DecoratorInterface
     */
    private $tokenDecorator;

    /**
     * @var TestApiKeyInterface
     */
    private $testApiKey;

    /**
     * @param Config              $config
     * @param DecoratorInterface  $tokenDecorator
     * @param TestApiKeyInterface $testApiKey
     */
    public function __construct(
        Config $config,
        DecoratorInterface $tokenDecorator,
        TestApiKeyInterface $testApiKey
    ) {
        $this->config = $config;
        $this->tokenDecorator = $tokenDecorator;
        $this->testApiKey = $testApiKey;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return __('Access Token')->render();
    }

    /**
     * @inheritdoc
     */
    public function getAuthHeader(): ?string
    {
        $token = $this->config->getAccessToken();

        return $token ? $this->tokenDecorator->decorate($token) : null;
    }

    /**
     * @inheritdoc
     */
    public function testConnection(array $params): bool
    {
        $apiUrl = $params['api_url'] ?? '';
        $token = $params['access_token'] ?? '';

        if ($token === self::OBSCURED_KEY) {
            $token = $this->config->getAccessToken();
        }

        $apiKey = $this->tokenDecorator->decorate($token);

        return $this->testApiKey->execute($apiUrl, $apiKey);
    }

    /**
     * @inheritdoc
     */
    public function validate(): void
    {
        if (!$this->getAuthHeader()) {
            throw new LocalizedException(__('Please provide the Access Token.'));
        }
    }
}
