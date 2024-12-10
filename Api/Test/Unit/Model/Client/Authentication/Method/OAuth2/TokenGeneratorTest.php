<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method\OAuth2;

use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenGenerator;
use Mirakl\Core\Client\AuthApiClient;
use Mirakl\Core\Client\AuthApiClientFactory;
use Mirakl\Core\Domain\Auth\ClientCredentials;
use Mirakl\Core\Request\Auth\ClientCredentialsRequest;
use Mirakl\Core\Request\Auth\ClientCredentialsRequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenGenerator
 */
class TokenGeneratorTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::generate
     * @covers ::cleanAuthUrl
     */
    public function testGenerate()
    {
        $clientCredentialsMock = $this->createMock(ClientCredentials::class);

        $authApiClientMock = $this->createMock(AuthApiClient::class);
        $authApiClientMock->expects($this->once())
            ->method('getCredentials')
            ->willReturn($clientCredentialsMock);

        $authApiClientFactoryMock = $this->createMock(AuthApiClientFactory::class);
        $authApiClientFactoryMock->expects($this->once())
            ->method('create')
            ->with(['baseUrl' => 'https://dummy.url'])
            ->willReturn($authApiClientMock);

        $clientCredentialsRequestMock = $this->createMock(ClientCredentialsRequest::class);

        $clientCredentialsRequestFactoryMock = $this->createMock(ClientCredentialsRequestFactory::class);
        $clientCredentialsRequestFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'client_id'     => 'foo',
                'client_secret' => 'bar'
            ])
            ->willReturn($clientCredentialsRequestMock);

        $tokenGenerator = new TokenGenerator($authApiClientFactoryMock, $clientCredentialsRequestFactoryMock);

        $credentials = $tokenGenerator->generate('foo', 'bar', 'https://dummy.url');

        $this->assertInstanceOf(ClientCredentials::class, $credentials);
    }
}
