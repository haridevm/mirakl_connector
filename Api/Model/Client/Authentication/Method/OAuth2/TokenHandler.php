<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method\OAuth2;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePoolInterface;

class TokenHandler implements TokenHandlerInterface
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
     * @var StoragePoolInterface
     */
    private $storagePool;

    /**
     * @param Config                  $config
     * @param TokenGeneratorInterface $tokenGenerator
     * @param StoragePoolInterface    $storagePool
     */
    public function __construct(
        Config $config,
        TokenGeneratorInterface $tokenGenerator,
        StoragePoolInterface $storagePool
    ) {
        $this->config = $config;
        $this->tokenGenerator = $tokenGenerator;
        $this->storagePool = $storagePool;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function refresh(): void
    {
        $clientId = $this->config->getOAuth2ClientId();
        $clientSecret = $this->config->getOAuth2ClientSecret();
        $authUrl = $this->config->getOAuth2AuthUrl();

        if (!$clientId || !$clientSecret || !$authUrl) {
            throw new LocalizedException(
                __("The following OAuth2 parameters are mandatory: 'client_id', 'client_secret' and 'auth_url'")
            );
        }

        // Generate a new access token with current settings
        $credentials = $this->tokenGenerator->generate($clientId, $clientSecret, $authUrl);

        // Save access token
        $this->storagePool->get('access_token')->save($credentials->getAccessToken());

        // Save token expiration date
        $date = $this->buildExpirationDate($credentials->getExpiresIn());
        $this->storagePool->get('expiration_date')->save($date->format(\DateTime::ATOM));
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        foreach ($this->storagePool->getAll() as $tokenStorage) {
            $tokenStorage->reset();
        }
    }

    /**
     * @param int $delay
     * @return \DateTimeInterface
     * @throws \Exception
     */
    private function buildExpirationDate(int $delay): \DateTimeInterface
    {
        $date = new \DateTime();

        // Add delay in seconds to current date
        $dateInterval = new \DateInterval(sprintf('PT%dS', $delay));
        $date->add($dateInterval);

        return $date;
    }
}