<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\AccessToken;
use Mirakl\Api\Model\Client\Authentication\Method\MethodInterface;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Decorator\DecoratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\AccessToken
 * @covers ::__construct
 */
class AccessTokenTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DecoratorInterface|MockObject
     */
    private $tokenDecoratorMock;

    /**
     * @var TestApiKeyInterface|MockObject
     */
    private $testApiKeyMock;

    /**
     * @var AccessToken|MockObject
     */
    private $accessToken;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->tokenDecoratorMock = $this->createMock(DecoratorInterface::class);
        $this->testApiKeyMock = $this->createMock(TestApiKeyInterface::class);
        $this->accessToken = new AccessToken($this->configMock, $this->tokenDecoratorMock, $this->testApiKeyMock);
    }

    /**
     * @covers ::getLabel
     */
    public function testGetLabel()
    {
        $this->assertSame('Access Token', $this->accessToken->getLabel());
    }

    /**
     * @covers ::getAuthHeader
     */
    public function testGetAuthHeader()
    {
        $this->configMock->expects($this->exactly(2))
            ->method('getAccessToken')
            ->willReturnOnConsecutiveCalls('', 'foo');

        $this->tokenDecoratorMock->expects($this->once())
            ->method('decorate')
            ->with('foo')
            ->willReturn('foobar');

        $this->assertNull($this->accessToken->getAuthHeader());
        $this->assertSame('foobar', $this->accessToken->getAuthHeader());
    }

    /**
     * @covers ::testConnection
     */
    public function testTestConnection()
    {
        $this->configMock->expects($this->once())
            ->method('getAccessToken');

        $this->testApiKeyMock->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->tokenDecoratorMock->expects($this->exactly(2))
            ->method('decorate')
            ->willReturnArgument(0);

        $this->assertTrue($this->accessToken->testConnection([
            'api_url'      => 'https://dummy.url',
            'access_token' => 'foo',
        ]));

        $this->assertFalse($this->accessToken->testConnection([
            'api_url'      => 'https://dummy.url',
            'access_token' => MethodInterface::OBSCURED_KEY,
        ]));
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $this->configMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('foo');

        $this->tokenDecoratorMock->expects($this->once())
            ->method('decorate')
            ->willReturn('foobar');

        $this->accessToken->validate();

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateFailed()
    {
        $this->configMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide the Access Token.');

        $this->accessToken->validate();
    }
}
