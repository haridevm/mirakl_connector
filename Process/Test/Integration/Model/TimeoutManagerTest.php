<?php
namespace Mirakl\Process\Test\Integration\Model;

use Magento\Framework\App\ResourceConnection;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\TimeoutManager;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\TimeoutManager
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
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->timeoutManager = $this->objectManager->get(TimeoutManager::class);
        $this->resource = $this->objectManager->get(ResourceConnection::class);
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testSimpleShortProcessTimeout()
    {
        // Create a sample short process for test, example: S20
        $process = $this->createSampleProcess(null, 'S20');
        $process->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process);

        // Change last update date for process to simulate a timeout
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');

        // Force updated_at in database
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $this->processResourceFactory->create()->load($process, $process->getId());
        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testSimpleShortProcessTimeoutWithHash()
    {
        $process = $this->createSampleProcess(null, 'S20');

        $process->setStatus(Process::STATUS_PROCESSING);
        $process->setHash('7215ee9c7d9dc229d2921a40e899ec5f');
        $this->processResourceFactory->create()->save($process);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout('7215ee9c7d9dc229d2921a40e899ec5f');

        $this->processResourceFactory->create()->load($process, $process->getId());
        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/timeout_delay 250
     */
    public function testSimpleLongProcessTimeout()
    {
        // Create a sample long process for test, example: CM51
        $process = $this->createSampleProcess(null, 'CM51');
        $process->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 251)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $this->processResourceFactory->create()->load($process, $process->getId());
        $this->assertTrue($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testParentProcessWithProcessingChildTimeout()
    {
        // Create parent process
        $process = $this->createSampleProcess(null, 'OF51');
        $process->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process);

        // Create 1 processing child
        $childProcess = $this->createSampleProcess($process->getId());
        $childProcess->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($childProcess);

        // Simulate timeout on parent process
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        $this->processResourceFactory->create()->load($process, $process->getId());

        // Parent process should not be set as timeout
        $this->assertFalse($process->isTimeout());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/short_timeout_delay 10
     */
    public function testChildProcessInTimeoutWithOtherPendingAndIdleChildren()
    {
        // Create parent process
        $process = $this->createSampleProcess(null, 'OF51');
        $process->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process);

        // Create 1 processing child
        $processingChild = $this->createSampleProcess($process->getId());
        $processingChild->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($processingChild);

        // Create 1 pending child
        $pendingChild = $this->createSampleProcess($process->getId());

        // Create 1 idle child
        $idleChild = $this->createSampleProcess($process->getId());
        $idleChild->setStatus(Process::STATUS_IDLE);
        $this->processResourceFactory->create()->save($idleChild);

        // Simulate timeout on processing child
        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 11)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $processingChild->getId()]
        );

        $this->timeoutManager->applyTimeout();

        // Processing child should be set as timeout
        $this->processResourceFactory->create()->load($processingChild, $processingChild->getId());
        $this->assertTrue($processingChild->isTimeout());

        // Parent process should be set as timeout
        $this->processResourceFactory->create()->load($process, $process->getId());
        $this->assertTrue($process->isTimeout());

        // Pending child should be cancelled
        $this->processResourceFactory->create()->load($pendingChild, $pendingChild->getId());
        $this->assertTrue($pendingChild->isCancelled());

        // Idle child should be cancelled
        $this->processResourceFactory->create()->load($idleChild, $idleChild->getId());
        $this->assertTrue($idleChild->isCancelled());
    }

    /**
     * @covers ::applyTimeout
     * @magentoConfigFixture current_store mirakl_process/general/timeout_delay null
     */
    public function testProcessWithoutTimeout()
    {
        $process = $this->createSampleProcess(null, 'CM51');
        $process->setStatus(Process::STATUS_PROCESSING);
        $this->processResourceFactory->create()->save($process);

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', 10000)));
        $timeoutDate = $timeoutDate->format('Y-m-d H:i:s');
        $connection = $this->resource->getConnection();
        $connection->update(
            $connection->getTableName('mirakl_process'),
            ['updated_at' => $timeoutDate],
            ['id = ?' => (int) $process->getId()]
        );

        $this->timeoutManager->applyTimeout();

        // Process should not set as timeout if no delay is configured
        $this->processResourceFactory->create()->load($process, $process->getId());
        $this->assertFalse($process->isTimeout());
    }
}
