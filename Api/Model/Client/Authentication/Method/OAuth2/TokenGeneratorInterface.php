<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Method\OAuth2;

use Mirakl\Core\Domain\Auth\ClientCredentials;

interface TokenGeneratorInterface
{
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $authUrl
     * @return ClientCredentials
     */
    public function generate(string $clientId, string $clientSecret, string $authUrl): ClientCredentials;
}