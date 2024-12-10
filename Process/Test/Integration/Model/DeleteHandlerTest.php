<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\DeleteHandler;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\DeleteHandler
 * @covers ::__construct
 */
class DeleteHandlerTest extends TestCase
{
    /**
     * @var DeleteHandler
     */
    private $deleteHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteHandler = $this->objectManager->create(DeleteHandler::class);
    }

    /**
     * @covers ::executeAll
     * @covers \Mirakl\Process\Model\ResourceModel\Process::truncate
     * @magentoDbIsolation disabled
     */
    public function testExecuteAll()
    {
        $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();

        $this->assertCount(3, $this->processCollectionFactory->create());

        $this->deleteHandler->executeAll();

        $this->assertCount(0, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::executeList
     * @magentoDbIsolation enabled
     */
    public function testExecuteList()
    {
        $this->createSampleProcess();
        $process = $this->createSampleProcess();
        $this->createSampleProcess();

        $processes = $this->processCollectionFactory->create()
            ->addIdFilter($process->getId())
            ->getData();

        $this->assertCount(1, $processes);

        $this->deleteHandler->executeList($processes);

        $this->assertCount(2, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::executeList
     * @magentoDbIsolation enabled
     */
    public function testExecuteListWithEmptyList()
    {
        $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();

        $this->assertCount(3, $this->processCollectionFactory->create());

        $this->deleteHandler->executeList([]);

        $this->assertCount(3, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::executeCollection
     * @magentoDbIsolation enabled
     */
    public function testExecuteCollection()
    {
        $this->createSampleProcess();
        $this->createSampleProcess();
        $process = $this->createSampleProcess();
        $this->createSampleProcess();

        $collection = $this->processCollectionFactory->create()
            ->addIdFilter($process->getId());

        $this->assertCount(1, $collection);

        $this->deleteHandler->executeCollection($collection);

        $this->assertCount(3, $this->processCollectionFactory->create());
    }
}
