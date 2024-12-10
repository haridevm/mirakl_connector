<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Token\Storage;

use Magento\Framework\Encryption\EncryptorInterface;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\ConfigStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Token\Storage\ConfigStorage
 * @covers ::__construct
 */
class ConfigStorageTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->encryptorMock = $this->createMock(EncryptorInterface::class);
    }

    /**
     * @covers ::load
     */
    public function testLoad()
    {
        $this->configMock->expects($this->exactly(5))
            ->method('getRawValue')
            ->with('foo/bar/baz')
            ->willReturnOnConsecutiveCalls(null, '', '0', 10, 'foo');

        $configStorage = new ConfigStorage($this->configMock, $this->encryptorMock, 'foo/bar/baz');

        $this->assertNull($configStorage->load());
        $this->assertNull($configStorage->load());
        $this->assertSame('0', $configStorage->load());
        $this->assertSame('10', $configStorage->load());
        $this->assertSame('foo', $configStorage->load());
    }

    /**
     * @covers ::load
     */
    public function testLoadWithEncryption()
    {
        $this->configMock->expects($this->once())
            ->method('getRawValue')
            ->with('foo/bar/baz')
            ->willReturn('encrypted');

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with('encrypted')
            ->willReturn('foo');

        $configStorage = new ConfigStorage($this->configMock, $this->encryptorMock, 'foo/bar/baz', true);

        $this->assertSame('foo', $configStorage->load());
    }

    /**
     * @covers ::save
     */
    public function testSave()
    {
        $this->configMock->expects($this->once())
            ->method('setValue')
            ->with('foo/bar/baz', 'foo');

        $this->encryptorMock->expects($this->never())
            ->method('encrypt');

        $configStorage = new ConfigStorage($this->configMock, $this->encryptorMock, 'foo/bar/baz');
        $configStorage->save('foo');
    }

    /**
     * @covers ::save
     */
    public function testSaveWithEncryption()
    {
        $this->configMock->expects($this->once())
            ->method('setValue')
            ->with('foo/bar/baz', 'encrypted');

        $this->encryptorMock->expects($this->once())
            ->method('encrypt')
            ->with('foo')
            ->willReturn('encrypted');

        $configStorage = new ConfigStorage($this->configMock, $this->encryptorMock, 'foo/bar/baz', true);
        $configStorage->save('foo');
    }

    /**
     * @covers ::reset
     */
    public function testReset()
    {
        $this->configMock->expects($this->once())
            ->method('deleteValue')
            ->with('foo/bar/baz');

        $configStorage = new ConfigStorage($this->configMock, $this->encryptorMock, 'foo/bar/baz');
        $configStorage->reset();
    }
}
