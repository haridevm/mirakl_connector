<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\ResourceModel;

use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\ResourceModel\Process
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProcessTest extends TestCase
{
    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->processResource = $this->processResourceFactory->create();
    }

    /**
     * @covers ::deleteIds
     */
    public function testDeleteIdsReturnsFalse()
    {
        $this->assertFalse($this->processResource->deleteIds([]));
    }

    /**
     * @covers ::deleteIds
     */
    public function testDeleteIds()
    {
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();
        $process3 = $this->createSampleProcess();

        $this->assertNotEmpty($process1->getId());
        $this->assertNotEmpty($process2->getId());
        $this->assertNotEmpty($process3->getId());

        $this->processResource->deleteIds([$process1->getId(), $process3->getId()]);

        $process1 = $this->getProcessById($process1->getId());
        $process2 = $this->getProcessById($process2->getId());
        $process3 = $this->getProcessById($process3->getId());

        $this->assertNull($process1->getId());
        $this->assertNotNull($process2->getId());
        $this->assertNull($process3->getId());
    }

    /**
     * @covers ::markAsTimeout
     */
    public function testMarkAsTimeoutWithEmptyDelay()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delay for expired processes cannot be empty');

        $this->processResource->markAsTimeout(0);
    }

    /**
     * @covers ::markAsTimeout
     */
    public function testMarkAsTimeout()
    {
        $process1 = $this->createSampleProcess(null, Process::STATUS_PROCESSING);
        $process2 = $this->createSampleProcess();
        $process3 = $this->createSampleProcess(null, Process::STATUS_COMPLETED);
        $process4 = $this->createSampleProcess(null, Process::STATUS_PROCESSING);

        // Set updated_at date to 120 minutes BEFORE the current date
        $updatedAt = new \DateTime();
        $updatedAt->sub(new \DateInterval(sprintf('PT%dM', 120)));
        $updatedAt = $updatedAt->format('Y-m-d H:i:s');

        $this->processResource->getConnection()->update(
            $this->processResource->getMainTable(),
            ['updated_at' => $updatedAt],
            ['id IN (?)' => [$process1->getId(), $process3->getId()]]
        );

        $this->processResource->markAsTimeout(60);

        $process1 = $this->getProcessById($process1->getId());
        $process2 = $this->getProcessById($process2->getId());
        $process3 = $this->getProcessById($process3->getId());
        $process4 = $this->getProcessById($process4->getId());

        $this->assertTrue($process1->isTimeout());
        $this->assertTrue($process2->isPending());
        $this->assertTrue($process3->isCompleted());
        $this->assertTrue($process4->isProcessing());
    }
}
