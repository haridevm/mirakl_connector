<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Execution;
use Mirakl\Process\Model\Output\NullOutput;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\Model\Action\ChildProcessExceptionActionStub;
use Mirakl\Process\Test\Integration\Model\Action\ExceptionActionStub;
use Mirakl\Process\Test\Integration\Model\Action\StopExecutionActionStub;
use Mirakl\Process\Test\Integration\Model\Action\UserErrorActionStub;
use Mirakl\Process\Test\Integration\Model\Action\UserNoticeActionStub;
use Mirakl\Process\Test\Integration\Model\Action\WarningActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ExecutionTest extends TestCase
{
    /**
     * @var Execution
     */
    private $execution;

    /**
     * @var Execution\Stack
     */
    private $stack;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->stack = $this->objectManager->create(Execution\Stack::class);
        $this->execution = $this->objectManager->create(Execution::class, [
            'stack' => $this->stack,
        ]);
    }

    /**
     * @covers ::run
     * @covers ::execute
     * @covers ::initErrorHandler
     */
    public function testErrorHandlerCatchUserError()
    {
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, new UserErrorActionStub());

        $this->execution->run($process);

        $this->assertTrue($process->isError());
        $this->assertStringContainsString('This is a sample user error', $process->getOutput());
    }

    /**
     * @covers ::run
     * @covers ::initErrorHandler
     */
    public function testErrorHandlerCatchWarning()
    {
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, new WarningActionStub());

        $this->execution->run($process);

        $this->assertTrue($process->isCompleted());
        $this->assertStringContainsString('Undefined variable $foo', $process->getOutput());
    }

    /**
     * @covers ::run
     * @covers ::initErrorHandler
     */
    public function testErrorHandlerDoNotCatchUserNotice()
    {
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, new UserNoticeActionStub());

        $this->execution->run($process);

        $this->assertTrue($process->isCompleted());
        $this->assertStringNotContainsString('This is a sample user notice', $process->getOutput());
        $this->assertStringContainsString('Just triggered an invisible notice', $process->getOutput());
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $process = $this->createSampleProcess();

        $this->execution->run($process);

        $this->assertTrue($process->isCompleted());
    }

    /**
     * @covers ::start
     */
    public function testStart()
    {
        $process = $this->createSampleProcess();

        $this->execution->start($process);

        $this->assertTrue($process->isStarted());
        $this->assertSame($process, $this->stack->current());
    }

    /**
     * @covers ::start
     */
    public function testStartWithProcessEnded()
    {
        $process = $this->createSampleProcess();
        $process->setStatus(Process::STATUS_COMPLETED);

        $this->execution->start($process);

        $this->assertTrue($process->isEnded());
        $this->assertSame($process, $this->stack->current());
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $process = $this->createSampleProcess();

        $this->assertSame(['foo'], $this->execution->execute($process));
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithStopExecutionException()
    {
        $stopActionStub = new StopExecutionActionStub();

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $stopActionStub);

        $result = $this->execution->execute($process);

        $this->assertTrue($process->isStopped());
        $this->assertStringContainsString('Stop me please!', $process->getOutput());
        $this->assertSame([], $result);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithChildProcessException()
    {
        $childExceptionStub = new ChildProcessExceptionActionStub();

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $childExceptionStub);

        $result = $this->execution->execute($process);

        $this->assertTrue($process->isError());
        $this->assertStringContainsString('I screwed up, forgive me.', $process->getOutput());
        $this->assertSame([], $result);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithException()
    {
        $exceptionStub = new ExceptionActionStub();

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $exceptionStub);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Damn! I am an exception.');

        $this->execution->execute($process);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithOutputBuffering()
    {
        $actionStub = new class extends ActionStub {
            public function execute(Process $process, ...$params): array
            {
                echo 'Test output'; // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput

                return [];
            }
        };

        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $actionStub);

        ob_start();
        $this->execution->execute($process);
        ob_end_clean();

        // Assert that the output was captured and flushed
        $this->assertStringContainsString('Test output', $process->getOutput());
    }

    /**
     * @covers ::stop
     */
    public function testStop()
    {
        $process = $this->createSampleProcess();
        $process->addOutput(NullOutput::class);

        $this->execution->stop($process);

        $this->assertTrue($process->getStopped());
        $this->assertStringContainsString('Memory Peak Usage:', $process->getOutput());
        $this->assertNull($this->stack->current());
    }

    /**
     * @covers ::stop
     */
    public function testStopProcessAlreadyStopped()
    {
        $process = $this->createSampleProcess();
        $process->setStopped();

        $this->execution->stop($process);

        $this->assertNull($process->getOutput());
    }

    /**
     * @covers ::cancel
     */
    public function testCancel()
    {
        $process = $this->createSampleProcess();

        $this->execution->cancel($process, 'Cancelled');

        $this->assertTrue($process->isCancelled());
        $this->assertStringContainsString('Cancelled', $process->getOutput());
    }

    /**
     * @covers ::fail
     */
    public function testFail()
    {
        $process = $this->createSampleProcess();

        $this->execution->fail($process, 'Failed');

        $this->assertTrue($process->isError());
        $this->assertStringContainsString('Failed', $process->getOutput());
    }
}
