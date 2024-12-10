<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ChildProcessExceptionActionStub;
use Mirakl\Process\Test\Integration\Model\Action\RetryLaterActionStub;
use Mirakl\Process\Test\Integration\Model\Action\StopExecutionActionStub;
use Mirakl\Process\Test\Integration\TestCase;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\AbstractParentAction
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AbstractParentActionTest extends TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $parentActionMock = $this->createParentActionMock([new ActionStub()]);
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);

        $this->assertSame(['foo'], $process->run());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithAlreadyCompletedChild()
    {
        $actionStub = new ActionStub();
        $parentActionMock = $this->createParentActionMock([$actionStub]);

        $process1 = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $this->createSampleProcess($process1->getId(), Process::STATUS_COMPLETED, $actionStub);

        $this->assertSame([], $process1->run());
    }

    /**
     * @covers ::execute
     * @covers ::handleRetryLater
     */
    public function testExecuteWithRetryLaterException()
    {
        $data = [
            'retry_count' => 2,
            'max_retry' => 5,
        ];

        $retryLaterActionStub = new RetryLaterActionStub($data);

        $parentActionMock = $this->createParentActionMock([$retryLaterActionStub]);

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $process->run();

        $this->assertTrue($process->isStatusPendingRetry());
        $this->assertStringContainsString('Retry later please.', $process->getOutput());
    }

    /**
     * @covers ::execute
     * @covers ::handleRetryLater
     */
    public function testExecuteWithMaxRetryCountReached()
    {
        $data = [
            'retry_count' => 2,
            'max_retry' => 2,
        ];

        $retryLaterActionStub = new RetryLaterActionStub($data);

        $parentActionMock = $this->createParentActionMock([$retryLaterActionStub]);

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $process->run();

        $this->assertTrue($process->isCancelled());
        $this->assertStringContainsString('has reached the max allowed retry count', $process->getOutput());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithStopExecutionException()
    {
        $stopActionStub = new StopExecutionActionStub();

        $parentActionMock = $this->createParentActionMock([$stopActionStub]);

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $process->run();

        $this->assertTrue($process->isCompleted());
        $this->assertStringContainsString('Stop me please!', $process->getOutput());
        $this->assertTrue($process->getChildrenCollection()->getFirstItem()->isStopped());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithChildProcessException()
    {
        $childExceptionStub = new ChildProcessExceptionActionStub();

        $parentActionMock = $this->createParentActionMock([$childExceptionStub]);

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $process->run();

        $this->assertTrue($process->isError());
        $this->assertStringContainsString('I screwed up, forgive me.', $process->getOutput());
        $this->assertTrue($process->getChildrenCollection()->getFirstItem()->isError());
    }
}
