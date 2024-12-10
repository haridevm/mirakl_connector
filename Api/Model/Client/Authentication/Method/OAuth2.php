<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenGeneratorInterface;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Decorator\DecoratorInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePoolInterface;

class OAuth2 implements MethodInterface, ExpirableTokenInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var DecoratorInterface
     */
    private $tokenDecorator;

    /**
     * @var StoragePoolInterface
     */
    private $storagePool;

    /**
     * @var TestApiKeyInterface
     */
    private $testApiKey;

    /**
     * @param Config                  $config
     * @param TokenGeneratorInterface $tokenGenerator
     * @param DecoratorInterface      $tokenDecorator
     * @param StoragePoolInterface    $storagePool
     * @param TestApiKeyInterface     $testApiKey
     */
    public function __construct(
        Config $config,
        TokenGeneratorInterface $tokenGenerator,
        DecoratorInterface $tokenDecorator,
        StoragePoolInterface $storagePool,
        TestApiKeyInterface $testApiKey
    ) {
        $this->config = $config;
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenDecorator = $tokenDecorator;
        $this->storagePool = $storagePool;
        $this->testApiKey = $testApiKey;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return __('OAuth 2.0 Client')->render();
    }

    /**
     * @inheritdoc
     */
    public function getAuthHeader(): ?string
    {
        $token = $this->storagePool->get('access_token')->load();

        return $token ? $this->tokenDecorator->decorate($token) : null;
    }

    /**
     * @inheritdoc
     */
    public function testConnection(array $params): bool
    {
        $apiUrl = $params['api_url'] ?? '';
        $clientId = $params['client_id'] ?? '';
        $clientSecret = $params['client_secret'] ?? '';
        $authUrl = $params['auth_url'] ?? '';

        if ($clientSecret === self::OBSCURED_KEY) {
            $clientSecret = $this->config->getOAuth2ClientSecret();
        }

        if (!$clientId || !$clientSecret || !$authUrl) {
            return false;
        }

        $credentials = $this->tokenGenerator->generate($clientId, $clientSecret, $authUrl);
        $apiKey = $this->tokenDecorator->decorate($credentials->getAccessToken());

        return $this->testApiKey->execute($apiUrl, $apiKey);
    }

    /**
     * @inheritdoc
     */
    public function isTokenExpired(): bool
    {
        $expirationDate = $this->getTokenExpirationDate();

        return $expirationDate && $expirationDate->getTimestamp() < time();
    }

    /**
     * @inheritdoc
     */
    public function getTokenExpirationDate(): ?\DateTimeInterface
    {
        $expirationDate = $this->storagePool->get('expiration_date')->load();

        return $expirationDate ? new \DateTime($expirationDate) : null;
    }

    /**
     * @inheritdoc
     */
    public function validate(): void
    {
        $clientId = $this->config->getOAuth2ClientId();
        $clientSecret = $this->config->getOAuth2ClientSecret();
        $authUrl = $this->config->getOAuth2AuthUrl();

        if (!$clientId || !$clientSecret || !$authUrl) {
            throw new LocalizedException(
                __('Please provide the OAuth 2.0 settings (Client ID, Client Secret, Authentication URL).')
            );
        }

        if ($this->isTokenExpired()) {
            throw new LocalizedException(__('We detected an expired token. ' .
                'Please check your refresh token cron job configuration or change the API Authentication Method.'));
        }

        if (!$this->storagePool->get('access_token')->load()) {
            throw new LocalizedException(
                __('Invalid connection. Please check your OAuth 2.0 configuration (Client ID, Client Secret, Authentication URL).')
            );
        }
    }
}