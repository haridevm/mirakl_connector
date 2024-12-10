<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\DeleteHandler;
use Mirakl\Process\Model\HistoryClearer;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\HistoryClearer
 * @covers ::__construct
 * @covers ::deleteBeforeDate
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class HistoryClearerTest extends TestCase
{
    /**
     * @var HistoryClearer
     */
    private $historyClearer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->historyClearer = $this->objectManager->create(HistoryClearer::class);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteBeforeDate()
    {
        $process = $this->createSampleProcess();

        // Set created at 1 day AFTER current date
        $createdAt = (new \DateTime())->add(new \DateInterval('P1D'))
            ->format('Y-m-d H:i:s');

        $process->setCreatedAt($createdAt);
        $process->output('Process created at ' . $createdAt, true);

        $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();

        $this->assertCount(4, $this->processCollectionFactory->create());

        // Set before date 1 minute AFTER current date
        $beforeDate = (new \DateTime())->add(new \DateInterval('PT1M'))
            ->format('Y-m-d H:i:s');

        // Only the processes created before the before date should be deleted
        $this->historyClearer->execute($process, $beforeDate);

        $this->assertStringContainsString('created before', $process->getOutput());
        $this->assertStringContainsString('Done!', $process->getOutput());
        $this->assertCount(1, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteBeforeDateDoesNothing()
    {
        $process = $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();

        $this->assertCount(4, $this->processCollectionFactory->create());

        // Set before date 1 day BEFORE current date
        $beforeDate = (new \DateTime())
            ->sub(new \DateInterval('P1D'))
            ->format('Y-m-d H:i:s');

        // No processes should be deleted
        $this->historyClearer->execute($process, $beforeDate);

        $this->assertStringContainsString('created before', $process->getOutput());
        $this->assertStringContainsString('Done!', $process->getOutput());
        $this->assertCount(4, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithoutBeforeDate()
    {
        $process = $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();
        $this->createSampleProcess();

        $this->assertCount(4, $this->processCollectionFactory->create());

        $deleteHandlerMock = $this->createMock(DeleteHandler::class);
        $deleteHandlerMock->expects($this->once())
            ->method('executeAll')
            ->willReturnCallback(function () {
                $processResource = $this->processResourceFactory->create();
                $processResource->getConnection()->delete($processResource->getMainTable());
            });

        // All processes should be deleted
        $historyClearer = $this->objectManager->create(HistoryClearer::class, [
            'processCollectionFactory' => $this->processCollectionFactory,
            'deleteHandler' => $deleteHandlerMock,
        ]);
        $historyClearer->execute($process);

        $this->assertStringNotContainsString('created before', $process->getOutput());
        $this->assertStringContainsString('Done!', $process->getOutput());
        $this->assertCount(0, $this->processCollectionFactory->create());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithException()
    {
        $deleteHandlerMock = $this->createMock(DeleteHandler::class);
        $deleteHandlerMock->expects($this->once())
            ->method('executeAll')
            ->willThrowException(new \Exception('Failed to delete processes'));

        $historyClearer = $this->objectManager->create(HistoryClearer::class, [
            'processCollectionFactory' => $this->processCollectionFactory,
            'deleteHandler' => $deleteHandlerMock,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to delete processes');

        $historyClearer->execute();
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithExceptionCaught()
    {
        $process = $this->createSampleProcess();

        $deleteHandlerMock = $this->createMock(DeleteHandler::class);
        $deleteHandlerMock->expects($this->once())
            ->method('executeAll')
            ->willThrowException(new \Exception('Failed to delete processes'));

        $historyClearer = $this->objectManager->create(HistoryClearer::class, [
            'processCollectionFactory' => $this->processCollectionFactory,
            'deleteHandler' => $deleteHandlerMock,
        ]);

        $historyClearer->execute($process);

        $this->assertStringNotContainsString('created before', $process->getOutput());
        $this->assertStringNotContainsString('Done!', $process->getOutput());
        $this->assertStringContainsString('An error occurred:', $process->getOutput());
    }
}
