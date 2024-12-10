<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Magento\Framework\App\ResourceConnection;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\TimeoutManager;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\TimeoutManager
 * @covers ::__construct
 * @covers ::isProcessTimedOut
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class TimeoutManagerTest extends TestCase
{
    /**
     * @var TimeoutManager
     */
    protected $timeoutManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProcessResource
     */
    protected $processResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->timeoutManager = $this->objectManager->get(TimeoutManager::class);
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->processResource = $this->processResourceFactory->create();
    }

    /**
     * @covers ::applyTimeout
     * @covers \Mirakl\Process\Model\Process::isTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testSimpleShortProcessTimeout()
    {
        // Create a sample short process for test, example: S20
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);
        $process->setCode('S20');

        $this->processResource->save($process);

        // Change last update date for process to simulate a timeout
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        // Force updated_at in database
        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $process = $this->getProcessById($process->getId());

        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testSimpleShortProcessTimeoutWithHash()
    {
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        $process->setHash('7215ee9c7d9dc229d2921a40e899ec5f');
        $this->processResource->save($process);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout('7215ee9c7d9dc229d2921a40e899ec5f');

        $process = $this->getProcessById($process->getId());

        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @covers \Mirakl\Process\Model\Process::isTimeout
     * @covers \Mirakl\Process\Model\Process::markAsTimeout
     * @magentoConfigFixture current_store mirakl_process/general/timeout_delay 250
     */
    public function testSimpleLongProcessTimeout()
    {
        // Create a sample long process for test, example: CM51
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);
        $process->setCode('CM51');

        $this->processResource->save($process);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 251)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $process = $this->getProcessById($process->getId());

        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @covers \Mirakl\Process\Model\Process::isTimeout
     * @covers \Mirakl\Process\Model\Process::hasProcessingChild
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testParentProcessWithProcessingChildTimeout()
    {
        // Create parent process
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        // Create 1 processing child
        $this->createSampleProcess($process->getId(), Process::STATUS_PROCESSING);

        // Simulate timeout on parent process
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $process = $this->getProcessById($process->getId());

        // Parent process should not be set as timeout
        $this->assertFalse($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @covers \Mirakl\Process\Model\Process::isTimeout
     * @covers \Mirakl\Process\Model\Process::isEnded
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testChildProcessInTimeoutWithOtherPendingAndIdleChildren()
    {
        // Create parent process
        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        // Create 1 processing child
        $processingChild = $this->createSampleProcess($process->getId(), Process::STATUS_PROCESSING);

        // Create 1 pending child
        $pendingChild = $this->createSampleProcess($process->getId());

        // Create 1 idle child
        $idleChild = $this->createSampleProcess($process->getId(), Process::STATUS_IDLE);

        // Simulate timeout on processing child
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $processingChild->getId()]
        );

        $this->timeoutManager->applyTimeout();

        // Processing child should be set as timeout
        $processingChild = $this->getProcessById($processingChild->getId());
        $this->assertTrue($processingChild->isTimeout());

        // Parent process should be set as timeout
        $process = $this->getProcessById($process->getId());
        $this->assertTrue($process->isTimeout());
        $this->assertTrue($process->isEnded());

        // Pending child should be cancelled
        $pendingChild = $this->getProcessById($pendingChild->getId());
        $this->assertTrue($pendingChild->isCancelled());
        $this->assertTrue($pendingChild->isEnded());

        // Idle child should be cancelled
        $idleChild = $this->getProcessById($idleChild->getId());
        $this->assertTrue($idleChild->isCancelled());
        $this->assertTrue($pendingChild->isEnded());
    }

    /**
     * @covers ::applyTimeout
     * @covers \Mirakl\Process\Model\Process::isTimeout
     * @magentoConfigFixture current_store mirakl_process/general/timeout_delay null
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay null
     */
    public function testProcessWithoutTimeout()
    {
        $config = $this->objectManager->get(\Mirakl\Process\Helper\Config::class);
        $this->assertEmpty($config->getLongTimeoutDelay());

        $process = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 10000)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();
        $connection->update(
            $this->resource->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        // Process should not set as timeout if no delay is configured
        $process = $this->getProcessById($process->getId());
        $this->assertFalse($process->isTimeout());
    }

    /**
     * @covers ::isProcessTimedOut
     */
    public function testIsProcessTimedOutReturnsFalse()
    {
        $process = $this->createSampleProcess();

        $collection = $this->processCollectionFactory->create();
        $collection->addFieldToFilter('id', $process->getId());

        $processHelperMock = $this->createMock(\Mirakl\Process\Helper\Data::class);
        $processHelperMock->expects($this->once())
            ->method('getRunningProcesses')
            ->willReturn($collection);

        /** @var TimeoutManager $timeoutManager */
        $timeoutManager = $this->objectManager->create(TimeoutManager::class, [
            'processHelper' => $processHelperMock,
            'config' => $this->objectManager->get(\Mirakl\Process\Helper\Config::class),
        ]);

        $timeoutManager->applyTimeout();

        $this->assertEmpty($collection);
    }
}
