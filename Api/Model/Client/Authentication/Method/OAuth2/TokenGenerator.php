<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method\OAuth2;

use Mirakl\Core\Client\AuthApiClient;
use Mirakl\Core\Domain\Auth\ClientCredentials;
use Mirakl\Core\Request\Auth\ClientCredentialsRequest;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(string $clientId, string $clientSecret, string $authUrl): ClientCredentials
    {
        $client = new AuthApiClient($this->cleanAuthUrl($authUrl));
        $request = new ClientCredentialsRequest($clientId, $clientSecret);

        return $client->getCredentials($request);
    }

    /**
     * @param string $authUrl
     * @return string
     */
    private function cleanAuthUrl(string $authUrl): string
    {
        return sprintf('%s://%s',
            parse_url($authUrl, PHP_URL_SCHEME),
            parse_url($authUrl, PHP_URL_HOST)
        );
    }
}