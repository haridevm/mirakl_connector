<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Exception;

use Mirakl\Process\Model\ChildProcessFactoryInterface;
use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Exception\RetryLaterHandler;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\RetryLaterActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Exception\RetryLaterHandler
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RetryLaterHandlerTest extends TestCase
{
    /**
     * @covers ::handle
     */
    public function testHandleWithoutRetryableAction()
    {
        $process = $this->createSampleProcess();

        $exception = new ProcessException($process, __('An error occurred'));

        $retryLaterHandler = new RetryLaterHandler(
            $this->objectManager->create(ChildProcessFactoryInterface::class)
        );

        $retryLaterHandler->handle($exception);

        $this->assertTrue($process->isStopped());
    }

    /**
     * @covers ::handle
     * @dataProvider getTestProcessRetryableDataProvider
     *
     * @param int    $retryCount
     * @param int    $maxRetry
     * @param string $expectedParentStatus
     * @param array  $expectedChildrenStatuses
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testProcessRetryable(
        int $retryCount,
        int $maxRetry,
        string $expectedParentStatus,
        array $expectedChildrenStatuses
    ) {
        $data = [
            'retry_count' => $retryCount,
            'max_retry' => $maxRetry,
        ];

        $retryLaterActionStub = new RetryLaterActionStub($data);

        $parentActionMock = $this->createParentActionMock([$retryLaterActionStub]);

        // Create a sample process for test
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $parentActionMock);
        $process->run();

        $this->assertStringContainsString('Retry later please.', $process->getOutput());
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getTestProcessRetryableDataProvider()
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
