<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\ChildProcessFactory;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\ChildProcessFactory
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ChildProcessFactoryTest extends TestCase
{
    /**
     * @var ChildProcessFactory
     */
    private $childProcessFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->childProcessFactory = $this->objectManager->create(ChildProcessFactory::class);
    }

    /**
     * @covers ::create
     */
    public function testCreateThrowsException()
    {
        $this->expectException(ChildProcessException::class);
        $this->expectExceptionMessage('Cannot create a child process under an unknown parent.');

        $this->childProcessFactory->create(
            $this->objectManager->create(Process::class),
            $this->getMockBuilder(ActionInterface::class)->getMock()
        );
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $parent = $this->createSampleProcess();

        $actionStub = new ActionStub();
        $child = $this->childProcessFactory->create($parent, $actionStub);

        $this->assertInstanceOf(Process::class, $child);
        $this->assertTrue($child->isStatusIdle());
        $this->assertSame($parent->getId(), $child->getParentId());
        $this->assertSame($parent->getType(), $child->getType());
        $this->assertSame($actionStub->getName(), $child->getName());
        $this->assertSame(get_class($actionStub), $child->getHelper());
        $this->assertSame('execute', $child->getMethod());
        $this->assertSame($actionStub->getParams(), $child->getParams());
    }
}
