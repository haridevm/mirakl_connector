<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action\Execution;

use Mirakl\Process\Model\Action\Execution\ChildProviderInterface;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\Execution\ChildProvider
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ChildProviderTest extends TestCase
{
    /**
     * @covers ::get
     * @covers ::getChildProcessPending
     */
    public function testGetWithPendingChild()
    {
        $actionStub = new ActionStub();

        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId(), Process::STATUS_IDLE, $actionStub);

        /** @var ChildProviderInterface $childProvider */
        $childProvider = $this->objectManager->create(ChildProviderInterface::class);

        $childProcess = $childProvider->get($process1, $actionStub);
        $this->assertSame($process2->getId(), $childProcess->getId());
    }

    /**
     * @covers ::get
     * @covers ::getChildProcessCompleted
     */
    public function testGetWithChildAlreadyCompleted()
    {
        $actionStub = new ActionStub();

        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId(), Process::STATUS_COMPLETED, $actionStub);

        $this->assertEquals($process1->getId(), $process2->getParentId());

        /** @var ChildProviderInterface $childProvider */
        $childProvider = $this->objectManager->create(ChildProviderInterface::class);

        $childProcess = $childProvider->get($process1, $actionStub);
        $this->assertNull($childProcess);
    }

    /**
     * @covers ::get
     */
    public function testGetWithChildProcessCreation()
    {
        $actionStub = new ActionStub();

        $process = $this->createSampleProcess();

        /** @var ChildProviderInterface $childProvider */
        $childProvider = $this->objectManager->create(ChildProviderInterface::class);

        $childProcess = $childProvider->get($process, $actionStub);
        $this->assertInstanceOf(Process::class, $childProcess);
    }
}
