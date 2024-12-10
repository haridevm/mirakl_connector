<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Method;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\Api\Model\Client\Authentication\Method\MethodInterface;
use Mirakl\Api\Model\Client\Authentication\Method\MethodPool;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Method\MethodPool
 * @covers ::__construct
 */
class MethodPoolTest extends TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $methods = [
            'foo' => $this->createMock(MethodInterface::class),
        ];

        $methodPool = new MethodPool($methods);

        $this->assertInstanceOf(MethodInterface::class, $methodPool->get('foo'));
    }

    /**
     * @covers ::get
     */
    public function testGetWithException()
    {
        $methodPool = new MethodPool();

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Could not find method with code foo');

        $methodPool->get('foo');
    }

    /**
     * @covers ::getAll
     */
    public function testGetAll()
    {
        $methods = [
            'foo' => $this->createMock(MethodInterface::class),
            'bar' => $this->createMock(MethodInterface::class),
        ];

        $methodPool = new MethodPool($methods);

        $this->assertCount(2, $methodPool->getAll());
        $this->assertSame(['foo', 'bar'], array_keys($methodPool->getAll()));
    }
}
