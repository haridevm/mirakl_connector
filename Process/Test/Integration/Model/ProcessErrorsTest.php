<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\UserErrorActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Process
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ProcessErrorsTest extends TestCase
{
    /**
     * @covers ::run
     * @covers ::isError
     */
    public function testRunProcessWithUserError()
    {
        // Use a process action stub for test
        $actionStub = new UserErrorActionStub();

        // Create a sample process for test
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $actionStub);

        // Run the process, an error should occurred and mark the process as "error"
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
     * @covers ::fail
     * @covers ::isError
     * @covers ::isCancelled
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
}
