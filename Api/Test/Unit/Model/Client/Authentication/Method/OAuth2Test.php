<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\MethodInterface;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenGeneratorInterface;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Decorator\DecoratorInterface as TokenDecoratorInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StorageInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePoolInterface;
use Mirakl\Core\Domain\Auth\ClientCredentials;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\OAuth2
 * @covers ::__construct
 */
class OAuth2Test extends TestCase
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
     * @var TokenDecoratorInterface|MockObject
     */
    private $tokenDecoratorMock;

    /**
     * @var StoragePoolInterface|MockObject
     */
    private $storagePoolMock;

    /**
     * @var TestApiKeyInterface|MockObject
     */
    private $testApiKeyMock;

    /**
     * @var OAuth2
     */
    private $oauth2;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->tokenGeneratorMock = $this->createMock(TokenGeneratorInterface::class);
        $this->tokenDecoratorMock = $this->createMock(TokenDecoratorInterface::class);
        $this->storagePoolMock = $this->createMock(StoragePoolInterface::class);
        $this->testApiKeyMock = $this->createMock(TestApiKeyInterface::class);

        $this->oauth2 = new OAuth2(
            $this->configMock,
            $this->tokenGeneratorMock,
            $this->tokenDecoratorMock,
            $this->storagePoolMock,
            $this->testApiKeyMock
        );
    }

    /**
     * @covers ::getLabel
     */
    public function testGetLabel()
    {
        $this->assertSame('OAuth 2.0 Client', $this->oauth2->getLabel());
    }

    /**
     * @covers ::getAuthHeader
     */
    public function testGetAuthHeader()
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls('', 'foo');

        $this->storagePoolMock->expects($this->exactly(2))
            ->method('get')
            ->with('access_token')
            ->willReturn($storageMock);

        $this->tokenDecoratorMock->expects($this->once())
            ->method('decorate')
            ->with('foo')
            ->willReturn('foobar');

        $this->assertNull($this->oauth2->getAuthHeader());
        $this->assertSame('foobar', $this->oauth2->getAuthHeader());
    }

    /**
     * @covers ::testConnection
     */
    public function testTestConnectionSuccess()
    {
        $this->configMock->expects($this->never())
            ->method('getOAuth2ClientSecret');

        $this->testApiKeyMock->expects($this->once())
            ->method('execute')
            ->with('https://dummy.url', 'foo')
            ->willReturn(true);

        $clientCredentialsMock = $this->getMockBuilder(ClientCredentials::class)
            ->addMethods(['getAccessToken'])
            ->getMock();
        $clientCredentialsMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('foo');

        $this->tokenGeneratorMock->expects($this->once())
            ->method('generate')
            ->with('foo')
            ->willReturn($clientCredentialsMock);

        $this->tokenDecoratorMock->expects($this->once())
            ->method('decorate')
            ->with('foo')
            ->willReturnArgument(0);

        $result = $this->oauth2->testConnection([
            'api_url'       => 'https://dummy.url',
            'client_id'     => 'foo',
            'client_secret' => 'bar',
            'auth_url'      => 'https://dummy.url',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @covers ::testConnection
     */
    public function testTestConnectionWithInvalidParams()
    {
        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientSecret')
            ->willReturn('bar');

        $this->testApiKeyMock->expects($this->never())
            ->method('execute');

        $result = $this->oauth2->testConnection([
            'api_url'       => 'https://dummy.url',
            'client_id'     => 'foo',
            'client_secret' => MethodInterface::OBSCURED_KEY,
            'auth_url'      => '',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $this->mockValidConfig();

        $storageMock1 = $this->createMock(StorageInterface::class);
        $storageMock1->expects($this->once())
            ->method('load')
            ->willReturn(null);

        $storageMock2 = $this->createMock(StorageInterface::class);
        $storageMock2->expects($this->once())
            ->method('load')
            ->willReturn('foo');

        $this->storagePoolMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['expiration_date', $storageMock1],
                ['access_token', $storageMock2],
            ]);

        $this->oauth2->validate();

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithInvalidConfig()
    {
        $this->configMock->expects($this->once())
            ->method('getOAuth2ClientId')
            ->willReturn('');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide the OAuth 2.0 settings ' .
            '(Client ID, Client Secret, Authentication URL).');

        $this->oauth2->validate();
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithTokenExpired()
    {
        $this->mockValidConfig();

        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects($this->once())
            ->method('load')
            ->willReturn('2024-01-01 12:34:56'); // Expired date

        $this->storagePoolMock->expects($this->once())
            ->method('get')
            ->with('expiration_date')
            ->willReturn($storageMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('We detected an expired token. ' .
            'Please check your refresh token cron job configuration or change the API Authentication Method.');

        $this->oauth2->validate();
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithEmptyAccessToken()
    {
        $this->mockValidConfig();

        $storageMock1 = $this->createMock(StorageInterface::class);
        $storageMock1->expects($this->once())
            ->method('load')
            ->willReturn(null);

        $storageMock2 = $this->createMock(StorageInterface::class);
        $storageMock2->expects($this->once())
            ->method('load')
            ->willReturn('');

        $this->storagePoolMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['expiration_date', $storageMock1],
                ['access_token', $storageMock2],
            ]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid connection. Please check your OAuth 2.0 configuration ' .
            '(Client ID, Client Secret, Authentication URL).');

        $this->oauth2->validate();
    }

    /**
     * @covers ::isTokenExpired
     */
    public function testIsTokenExpired()
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects($this->exactly(3))
            ->method('load')
            ->willReturnOnConsecutiveCalls(null, '2024-01-01 12:34:56', '2050-12-31 01:23:45');

        $this->storagePoolMock->expects($this->exactly(3))
            ->method('get')
            ->with('expiration_date')
            ->willReturn($storageMock);

        $this->assertFalse($this->oauth2->isTokenExpired());
        $this->assertTrue($this->oauth2->isTokenExpired());
        $this->assertFalse($this->oauth2->isTokenExpired());
    }

    /**
     * @covers ::getTokenExpirationDate
     */
    public function testGetTokenExpirationDate()
    {
        $storageMock = $this->createMock(StorageInterface::class);
        $storageMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls(null, '2024-01-01 12:34:56');

        $this->storagePoolMock->expects($this->exactly(2))
            ->method('get')
            ->with('expiration_date')
            ->willReturn($storageMock);

        $this->assertNull($this->oauth2->getTokenExpirationDate());
        $this->assertInstanceOf(\DateTime::class, $this->oauth2->getTokenExpirationDate());
    }

    /**
     * @return void
     */
    private function mockValidConfig()
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
    }
}
