<?php
namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Action\AbstractParentAction;
use Mirakl\Process\Model\Action\ActionListInterface;
use Mirakl\Process\Model\Action\Execution\ChildProviderInterface;
use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\Action\RetryableTrait;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Exception\RetryLaterHandlerInterface;
use Mirakl\Process\Model\Execution\Executor;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Process
 */
class ProcessErrorsTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRunProcessWithUserError()
    {
        // Create a sample process for test
        $process = $this->createSampleProcess();

        // Mock the process helper method for test
        $helperMock = new class {
            public function run()
            {
                trigger_error('This is a sample user error', E_USER_ERROR);
            }
        };

        $process->setHelper(get_class($helperMock));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($helperMock);

        // Run the process, an error should occurred and mark the process has "error"
        try {
            $process->run();
        } catch (\Exception $e) {
            $this->assertStringContainsString('This is a sample user error', $e->getMessage());
        }

        // Process must have the status "error" and error message should be logged in process output
        $this->assertTrue($process->isError());
        $this->assertNotEmpty($process->getOutput());
    }

    /**
     * @coversNothing
     */
    public function testCancelChildrenProcessesInCascadeWhenParentFails()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         *
         * process #1
         *  |_ process #2
         *      |_ process #3
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());
        $process3 = $this->createSampleProcess($process2->getId());
        $process1->start();

        $process1->fail('Failing process #1 to test children automatic cascade cancellation');

        // Ensure that process #1 has failed and that other processes have been cancelled in cascade
        $this->assertEquals(Process::STATUS_ERROR, $this->getProcessById($process1->getId())->getStatus());
        $this->assertEquals(Process::STATUS_CANCELLED, $this->getProcessById($process2->getId())->getStatus());
        $this->assertEquals(Process::STATUS_CANCELLED, $this->getProcessById($process3->getId())->getStatus());
        $this->assertTrue($process1->isError());
        $this->assertTrue($this->getProcessById($process2->getId())->isCancelled());
        $this->assertTrue($this->getProcessById($process3->getId())->isCancelled());
    }

    /**
     * @dataProvider getTestProcessRetryableExhaustedDataProvider
     * @covers RetryableInterface
     *
     * @param int    $retryCount
     * @param int    $maxRetry
     * @param string $expectedParentStatus
     * @param array  $expectedChildrenStatuses
     * @return void
     */
    public function testProcessRetryable(
        int $retryCount,
        int $maxRetry,
        string $expectedParentStatus,
        array $expectedChildrenStatuses
    ) {
        // Create a sample process for test
        $process = $this->createSampleProcess();

        $data = [
            'retry_count' => $retryCount,
            'max_retry' => $maxRetry,
        ];

        $retryableAction = new class($data) extends AbstractAction implements RetryableInterface {
            use RetryableTrait;

            public function getName(): string
            {
                return 'Retryable action test';
            }

            public function execute(Process $process, ...$params): array
            {
                $process->output('Executing test for retryable action ...');

                throw new RetryLaterException($process, __('I am sleeping, please retry later.'));
            }
        };

        $helperMock = $this->getMockBuilder(AbstractParentAction::class)
            ->setConstructorArgs([
                'childProvider'     => $this->objectManager->create(ChildProviderInterface::class),
                'retryLaterHandler' => $this->objectManager->create(RetryLaterHandlerInterface::class),
                'executor'          => $this->objectManager->create(Executor::class),
                'actionList'        => $this->objectManager->create(ActionListInterface::class, [
                    'actions' => [$retryableAction],
                ]),
            ])
            ->getMockForAbstractClass();

        $process->setHelper(get_class($helperMock));
        $process->setMethod('execute');

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($helperMock);

        $process->run();

        $this->assertStringContainsString('Executing test for retryable action', $process->getOutput());
        $this->assertStringContainsString('I am sleeping, please retry later.', $process->getOutput());
        $this->assertSame($expectedParentStatus, $process->getStatus());

        $children = $process->getChildrenCollection();
        $this->assertCount(count($expectedChildrenStatuses), $children);

        $children = array_values($children->getItems());

        foreach ($expectedChildrenStatuses as $key => $expectedChildStatus) {
            /** @var Process $child */
            $child = $children[$key];
            $this->assertSame($expectedChildStatus, $child->getStatus());
        }
    }

    /**
     * @return array[]
     */
    public function getTestProcessRetryableExhaustedDataProvider()
    {
        return [
            [
                // Test a valid retryable process
                $retryCount = 2,
                $maxRetry = 4,
                $expectedParentStatus = Process::STATUS_PENDING_RETRY,
                $expectedChildrenStatuses = [Process::STATUS_STOPPED, Process::STATUS_IDLE],
            ],
            [
                // Test an exhausted retryable process
                $retryCount = 3,
                $maxRetry = 3,
                $expectedParentStatus = Process::STATUS_CANCELLED,
                $expectedChildrenStatuses = [Process::STATUS_STOPPED],
            ],
        ];
    }
}
