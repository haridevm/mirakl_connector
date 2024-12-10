<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method\OAuth2;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenGeneratorInterface;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenHandler;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\ConfigStorage;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StorageInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePoolInterface;
use Mirakl\Core\Domain\Auth\ClientCredentials;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenHandler
 * @covers ::__construct
 */
class TokenHandlerTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var TokenGeneratorInterface|MockObject
     */
    private $tokenGeneratorMock;

    /**
     * @var StoragePoolInterface|MockObject
     */
    private $storagePoolMock;

    /**
     * @var TokenHandler
     */
    private $tokenHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->tokenGeneratorMock = $this->createMock(TokenGeneratorInterface::class);
        $this->storagePoolMock = $this->createMock(StoragePoolInterface::class);
        $this->tokenHandler = new TokenHandler($this->configMock, $this->tokenGeneratorMock, $this->storagePoolMock);
    }

    /**
     * @covers ::refresh
     * @covers ::buildExpirationDate
     */
    public function testRefresh()
    {
        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientId')
            ->willReturn('foo');

        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientSecret')
            ->willReturn('bar');

        $this->configMock->expects($this->once())
            ->method('getOAuth2AuthUrl')
            ->willReturn('https://dummy.url');

        $clientCredentialsMock = $this->getMockBuilder(ClientCredentials::class)
            ->addMethods(['getAccessToken', 'getExpiresIn'])
            ->getMock();

        $clientCredentialsMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('foo');

        $clientCredentialsMock->expects($this->once())
            ->method('getExpiresIn')
            ->willReturn(3600);

        $this->tokenGeneratorMock->expects($this->once())
            ->method('generate')
            ->with('foo', 'bar', 'https://dummy.url')
            ->willReturn($clientCredentialsMock);

        $storageMock = $this->createMock(ConfigStorage::class);

        $this->storagePoolMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['access_token', $storageMock],
                ['expiration_date', $storageMock],
            ]);

        $this->tokenHandler->refresh();

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::refresh
     */
    public function testRefreshWithException()
    {
        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientId')
            ->willReturn('foo');

        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientSecret')
            ->willReturn('');

        $this->configMock->expects($this->once())
            ->method('getOAuth2AuthUrl')
            ->willReturn('https://dummy.url');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            "The following OAuth2 parameters are mandatory: 'client_id', 'client_secret' and 'auth_url'"
        );

        $this->tokenHandler->refresh();
    }

    /**
     * @covers ::reset
     */
    public function testReset()
    {
        $storageMock = $this->createMock(StorageInterface::class);

        $this->storagePoolMock->expects($this->once())
            ->method('getAll')
            ->willReturn([$storageMock]);

        $this->tokenHandler->reset();
    }
}
