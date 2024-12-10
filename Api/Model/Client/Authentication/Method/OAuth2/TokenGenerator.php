<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method\OAuth2;

use Mirakl\Core\Client\AuthApiClientFactory;
use Mirakl\Core\Domain\Auth\ClientCredentials;
use Mirakl\Core\Request\Auth\ClientCredentialsRequestFactory;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var AuthApiClientFactory
     */
    private $authApiClientFactory;

    /**
     * @var ClientCredentialsRequestFactory
     */
    private $clientCredentialsRequestFactory;

    /**
     * @param AuthApiClientFactory            $authApiClientFactory
     * @param ClientCredentialsRequestFactory $clientCredentialsRequestFactory
     */
    public function __construct(
        AuthApiClientFactory $authApiClientFactory,
        ClientCredentialsRequestFactory $clientCredentialsRequestFactory
    ) {
        $this->authApiClientFactory = $authApiClientFactory;
        $this->clientCredentialsRequestFactory = $clientCredentialsRequestFactory;
    }

    /**
     * @inheritdoc
     */
    public function generate(string $clientId, string $clientSecret, string $authUrl): ClientCredentials
    {
        $client = $this->authApiClientFactory->create([
            'baseUrl' => $this->cleanAuthUrl($authUrl),
        ]);

        $request = $this->clientCredentialsRequestFactory->create([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        return $client->getCredentials($request);
    }

    /**
     * @param string $authUrl
     * @return string
     */
    private function cleanAuthUrl(string $authUrl): string
    {
        return sprintf(
            '%s://%s',
            parse_url($authUrl, PHP_URL_SCHEME),
            parse_url($authUrl, PHP_URL_HOST)
        );
    }
}
