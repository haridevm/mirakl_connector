<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Token\Storage;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StorageInterface;
use Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePool;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Token\Storage\StoragePool
 * @covers ::__construct
 */
class StoragePoolTest extends TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $storages = [
            'foo' => $this->createMock(StorageInterface::class),
        ];

        $storagePool = new StoragePool($storages);

        $this->assertInstanceOf(StorageInterface::class, $storagePool->get('foo'));
    }

    /**
     * @covers ::get
     */
    public function testGetWithException()
    {
        $storagePool = new StoragePool();

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Could not find storage with code foo');

        $storagePool->get('foo');
    }

    /**
     * @covers ::getAll
     */
    public function testGetAll()
    {
        $storages = [
            'foo' => $this->createMock(StorageInterface::class),
            'bar' => $this->createMock(StorageInterface::class),
        ];

        $storagePool = new StoragePool($storages);

        $this->assertCount(2, $storagePool->getAll());
        $this->assertSame(['foo', 'bar'], array_keys($storagePool->getAll()));
    }
}
