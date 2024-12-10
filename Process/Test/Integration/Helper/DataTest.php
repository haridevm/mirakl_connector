<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Helper;

use Mirakl\Process\Helper\Data as Helper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group helper
 * @coversDefaultClass \Mirakl\Process\Helper\Data
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class DataTest extends TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = $this->objectManager->get(Helper::class);
    }

    /**
     * @covers ::getPendingProcess
     */
    public function testNoPendingProcessFound()
    {
        // No process is present in db, no pending process should be found
        // Do not use assertNull() to avoid to print object dump when test fail
        $this->assertTrue($this->helper->getPendingProcess() === null);
    }

    /**
     * @covers ::getPendingProcess
     * @magentoDataFixture Mirakl_Process::Test/Integration/_files/processes.php
     */
    public function testGetOlderPendingProcess()
    {
        // Retrieve all pending processes
        $processes = $this->findProcess('status', Process::STATUS_PENDING);

        // Ensure that both processes are in pending status
        $this->assertCount(2, $processes);
        $this->assertTrue($processes->getFirstItem()->isPending());
        $this->assertTrue($processes->getLastItem()->isPending());

        // Retrieve real pending process
        $pendingProcess = $this->helper->getPendingProcess();

        // Ensure that process #1 is the pending process because older than process #2
        $this->assertFalse($pendingProcess === null);
        $this->assertEquals($processes->getFirstItem()->getId(), $pendingProcess->getId());
    }

    /**
     * @covers ::getPendingProcess
     */
    public function testGetPendingProcessWithParentCompleted()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         *
         * process #1
         *  |_ process #2
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());

        // Ensure that both processes are in pending status
        $this->processResourceFactory->create()->load($process1, $process1->getId());
        $this->processResourceFactory->create()->load($process2, $process2->getId());
        $this->assertTrue($process1->isPending());
        $this->assertTrue($process2->isPending());

        // Ensure that process #2 is a child of process #1
        $this->assertEquals($process1->getId(), $process2->getParentId());

        $process1->run();

        // Ensure that process #1 has completed
        $this->assertTrue($process1->isCompleted());

        // Retrieve real pending process
        $pendingProcess = $this->helper->getPendingProcess();

        // Ensure that process #2 is the pending process
        $this->assertFalse($pendingProcess === null);
        $this->assertEquals($process2->getId(), $pendingProcess->getId());
    }

    /**
     * @covers ::getPendingProcess
     */
    public function testCannotGetPendingProcessWithTheSameHash()
    {
        // Create 2 sample processes for test
        $process1 = $this->createSampleProcess();
        $process1->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process1);
        $process2 = $this->createSampleProcess();

        // Ensure that statuses are correct
        $this->processResourceFactory->create()->load($process1, $process1->getId());
        $this->processResourceFactory->create()->load($process2, $process2->getId());
        $this->assertTrue($process1->isProcessing());
        $this->assertTrue($process2->isPending());

        // Retrieve real pending process
        $pendingProcess = $this->helper->getPendingProcess();

        // We should not have a pending process because processes have the same hash
        $this->assertTrue($pendingProcess === null);
    }

    /**
     * @covers ::getPendingProcess
     */
    public function testGetPendingProcessWithDifferentHash()
    {
        // Create 2 sample processes for test
        $process1 = $this->createSampleProcess();
        $method = 'md5';
        $process1->setStatus(Process::STATUS_PROCESSING)->setHash($method(uniqid()));
        $this->processResourceFactory->create()->save($process1);
        $process2 = $this->createSampleProcess();

        // Ensure that statuses are correct
        $this->processResourceFactory->create()->load($process1, $process1->getId());
        $this->processResourceFactory->create()->load($process2, $process2->getId());
        $this->assertTrue($process1->isProcessing());
        $this->assertTrue($process2->isPending());

        // Retrieve real pending process
        $pendingProcess = $this->helper->getPendingProcess();

        // Process #2 is the pending process because hash is different
        $this->assertFalse($pendingProcess === null);
        $this->assertEquals($process2->getId(), $pendingProcess->getId());
    }
}
