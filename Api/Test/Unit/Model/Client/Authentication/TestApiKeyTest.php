<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication;

use Mirakl\Api\Model\Client\Authentication\TestApiKey;
use Mirakl\MMP\Front\Client\FrontApiClient;
use Mirakl\MMP\Front\Client\FrontApiClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\TestApiKey
 * @covers ::__construct
 */
class TestApiKeyTest extends TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $frontApiClientMock = $this->createMock(FrontApiClient::class);
        $frontApiClientMock->expects($this->once())
            ->method('getVersion')
            ->willReturn('1.0.0');

        $frontApiClientFactoryMock = $this->createMock(FrontApiClientFactory::class);
        $frontApiClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($frontApiClientMock);

        $testApiKey = new TestApiKey($frontApiClientFactoryMock);

        $this->assertTrue($testApiKey->execute('https://dummy.url', 'foo'));
    }

    /**
     * @covers ::execute
     */
    public function testExecuteFailed()
    {
        $frontApiClientFactoryMock = $this->createMock(FrontApiClientFactory::class);

        $testApiKey = new TestApiKey($frontApiClientFactoryMock);

        $this->assertFalse($testApiKey->execute('', ''));
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithException()
    {
        $frontApiClientMock = $this->createMock(FrontApiClient::class);
        $frontApiClientMock->expects($this->once())
            ->method('getVersion')
            ->willThrowException(new \Exception('Could not get version'));

        $frontApiClientFactoryMock = $this->createMock(FrontApiClientFactory::class);
        $frontApiClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($frontApiClientMock);

        $testApiKey = new TestApiKey($frontApiClientFactoryMock);

        $this->assertFalse($testApiKey->execute('https://dummy.url', 'foo'));
    }
}
