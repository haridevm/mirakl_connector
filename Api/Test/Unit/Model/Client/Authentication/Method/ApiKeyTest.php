<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method;

use Magento\Framework\Exception\LocalizedException;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\ApiKey;
use Mirakl\Api\Model\Client\Authentication\TestApiKeyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\ApiKey
 * @covers ::__construct
 */
class ApiKeyTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var TestApiKeyInterface|MockObject
     */
    private $testApiKeyMock;

    /**
     * @var ApiKey
     */
    private $apiKey;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->testApiKeyMock = $this->createMock(TestApiKeyInterface::class);
        $this->apiKey = new ApiKey($this->configMock, $this->testApiKeyMock);
    }

    /**
     * @covers ::getLabel
     */
    public function testGetLabel()
    {
        $this->assertSame('API Front Key', $this->apiKey->getLabel());
    }

    /**
     * @covers ::getAuthHeader
     */
    public function testGetAuthHeader()
    {
        $this->configMock->expects($this->exactly(2))
            ->method('getApiKey')
            ->willReturnOnConsecutiveCalls('', 'foo');

        $this->assertNull($this->apiKey->getAuthHeader());
        $this->assertSame('foo', $this->apiKey->getAuthHeader());
    }

    /**
     * @covers ::testConnection
     */
    public function testTestConnection()
    {
        $this->testApiKeyMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->assertTrue($this->apiKey->testConnection([
            'api_url' => 'https://dummy.url',
            'api_key' => 'foo',
        ]));
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $this->configMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn('foo');

        $this->apiKey->validate();

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateFailed()
    {
        $this->configMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn('');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide the API Key.');

        $this->apiKey->validate();
    }
}
