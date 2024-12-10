<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\ResourceModel\Process;

use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\Model\Action\DelayableActionStub;
use Mirakl\Process\Test\Integration\Model\Action\ExceptionActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\ResourceModel\Process\Collection
 * @covers ::_construct
 * @covers ::_afterLoad
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CollectionTest extends TestCase
{
    /**
     * @covers ::addIdFilter
     */
    public function testAddIdFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();

        $this->assertNotEmpty($process2->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addIdFilter($process1->getId());

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process1->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addActionFilter
     */
    public function testAddActionFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess(null, Process::STATUS_PENDING, new DelayableActionStub());

        $this->assertNotEmpty($process2->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addActionFilter(new ActionStub());

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process1->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addApiTypeFilter
     */
    public function testAddApiTypeFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();

        $this->assertNotEmpty($process1->getId());
        $this->assertNotEmpty($process2->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addApiTypeFilter();

        $this->assertCount(0, $collection->getItems());
    }

    /**
     * @covers ::addCompletedFilter
     */
    public function testAddCompletedFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess(null, Process::STATUS_COMPLETED);

        $this->assertNotEmpty($process1->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addCompletedFilter();

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process2->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addExcludeHashFilter
     */
    public function testAddExcludeHashFilterWithEmptyHash()
    {
        $this->createSampleProcess();
        $this->createSampleProcess();

        $collection = $this->processCollectionFactory->create();
        $collection->addExcludeHashFilter('');

        $this->assertCount(2, $collection->getItems());
    }

    /**
     * @covers ::addExcludeHashFilter
     */
    public function testAddExcludeHashFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess(null, Process::STATUS_PENDING, new ExceptionActionStub());

        $collection = $this->processCollectionFactory->create();
        $collection->addExcludeHashFilter($process1->getHash());

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process2->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addIdleFilter
     */
    public function testAddIdleFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess(null, Process::STATUS_IDLE);

        $this->assertNotEmpty($process1->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addIdleFilter();

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process2->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addParentFilter
     */
    public function testAddParentFilter()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         * process #1
         *  |_ process #2
         *  |_ process #3
         * process #4
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());
        $process3 = $this->createSampleProcess($process1->getId());
        $process4 = $this->createSampleProcess();

        $this->assertNotEmpty($process2->getId());
        $this->assertNotEmpty($process2->getId());
        $this->assertNotEmpty($process4->getId());
        $this->assertSame($process1->getId(), $process2->getParentId());
        $this->assertSame($process1->getId(), $process3->getParentId());

        $collection = $this->processCollectionFactory->create();
        $collection->addParentFilter($process1->getId());

        $this->assertCount(2, $collection->getItems());
        $this->assertEquals([$process2->getId(), $process3->getId()], $collection->getAllIds());
    }

    /**
     * @covers ::addPendingFilter
     */
    public function testAddPendingFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess(null, Process::STATUS_PENDING_RETRY);
        $process3 = $this->createSampleProcess(null, Process::STATUS_IDLE);

        $this->assertNotEmpty($process3->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addPendingFilter();

        $this->assertCount(2, $collection->getItems());
        $this->assertEquals([$process1->getId(), $process2->getId()], $collection->getAllIds());
    }

    /**
     * @covers ::addProcessingFilter
     */
    public function testAddProcessingFilter()
    {
        $process1 = $this->createSampleProcess(null, Process::STATUS_PROCESSING);
        $process2 = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        $collection = $this->processCollectionFactory->create();
        $collection->addProcessingFilter();

        $this->assertCount(2, $collection->getItems());
        $this->assertEquals([$process1->getId(), $process2->getId()], $collection->getAllIds());
    }

    /**
     * @covers ::addMiraklProcessingFilter
     */
    public function testAddMiraklProcessingFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();
        $process2->setMiraklStatus(Process::STATUS_PROCESSING);
        $process2->output('Mirakl processing', true);

        $this->assertNotEmpty($process1->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addMiraklProcessingFilter();

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process2->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addMiraklPendingFilter
     */
    public function testAddMiraklPendingFilter()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();
        $process2->setMiraklStatus(Process::STATUS_PENDING);
        $process2->output('Mirakl processing', true);

        $this->assertNotEmpty($process1->getId());

        $collection = $this->processCollectionFactory->create();
        $collection->addMiraklPendingFilter();

        $this->assertCount(1, $collection->getItems());
        $this->assertEquals($process2->getId(), $collection->getFirstItem()->getId());
    }

    /**
     * @covers ::addStatusFilter
     */
    public function testAddStatusFilter()
    {
        $this->createSampleProcess(null, Process::STATUS_ERROR);
        $this->createSampleProcess(null, Process::STATUS_STOPPED);
        $this->createSampleProcess(null, Process::STATUS_IDLE);
        $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        $collection = $this->processCollectionFactory->create();
        $collection->addStatusFilter([Process::STATUS_ERROR, Process::STATUS_STOPPED]);

        $this->assertCount(2, $collection->getItems());
    }

    /**
     * @covers ::cancel
     */
    public function testCancel()
    {
        $this->createSampleProcess(null, Process::STATUS_ERROR);
        $this->createSampleProcess(null, Process::STATUS_STOPPED);
        $this->createSampleProcess(null, Process::STATUS_IDLE);
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        $collection = $this->processCollectionFactory->create();
        $collection->addStatusFilter(Process::STATUS_PROCESSING);

        $this->assertCount(1, $collection->getItems());

        $collection->cancel();

        $this->processResourceFactory->create()->load($process, $process->getId());

        $this->assertTrue($process->isCancelled());
    }
}
