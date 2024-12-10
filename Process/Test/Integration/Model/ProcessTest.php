<?php
namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Process
 */
class ProcessTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRunProcessWithParams()
    {
        // Create a sample process for test
        $process = $this->createSampleProcess();

        // Mock the process helper method for test
        $helperMock = new class {
            public function run(Process $process, $foo, $bar)
            {
                $process->output('This is a test');
                TestCase::assertTrue($process->isProcessing());
                TestCase::assertSame('foo', $foo);
                TestCase::assertSame(['bar'], $bar);
            }
        };

        // Ensure that process has been saved correctly in pending status and with params
        $this->processResourceFactory->create()->load($process, $process->getId());

        $process->setHelper(get_class($helperMock));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($helperMock);

        $this->assertNotEmpty($process->getId());
        $this->assertTrue($process->isPending());
        $this->assertNull($process->getParentId());
        $this->assertNotEmpty($process->getParams());

        // Run the process
        $process->run();

        // Process should be completed without any error
        $this->assertTrue($process->isCompleted());

        $this->assertGreaterThan(0, $process->getDuration());
        $this->assertNotEmpty($process->getOutput());
    }

    /**
     * @coversNothing
     */
    public function testDeleteParentProcessMustDeleteChildrenInCascade()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         *
         * process #1
         *  |_ process #2
         *  |_ process #3
         *      |_ process #4
         *  |_ process #5
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());
        $process3 = $this->createSampleProcess($process1->getId());
        $process4 = $this->createSampleProcess($process3->getId());
        $process5 = $this->createSampleProcess($process1->getId());

        // Delete the main process should delete all children in cascade
        $this->processResourceFactory->create()->delete($process1);

        $this->assertNull($this->getProcessById($process1->getId())->getId());
        $this->assertNull($this->getProcessById($process2->getId())->getId());
        $this->assertNull($this->getProcessById($process3->getId())->getId());
        $this->assertNull($this->getProcessById($process4->getId())->getId());
        $this->assertNull($this->getProcessById($process5->getId())->getId());
    }

    /**
     * @coversNothing
     */
    public function testChildrenIdAutomaticallyCreatedUnderCurrentProcess()
    {
        /**
         * Create sample processes created when parent process is running:
         *
         * process #1
         *  |_ process #2
         *    |_ process #3
         */
        $process1 = $this->createSampleProcess();
        $process1->start();
        $process2 = $this->createSampleProcess();
        $process2->start();
        $process3 = $this->createSampleProcess();
        $process3->start();
        $process3->stop();
        $process2->stop();
        $process1->stop();

        // Ensure that the three processes have been executed successfully
        $this->assertTrue($process1->isCompleted());
        $this->assertTrue($process2->isCompleted());
        $this->assertTrue($process3->isCompleted());

        // Ensure that process #2 and #3 are children of #1 and #2 respectively
        $this->assertEquals($process1->getId(), $process2->getParentId());
        $this->assertEquals($process2->getId(), $process3->getParentId());
    }

    /**
     * @coversNothing
     */
    public function testChildrenIdAutomaticallySetAndAssignedParentKept()
    {
        /**
         * Create sample parent/child processes created when parent process is running:
         *
         * process #1
         *  |_ process #2
         *  |_ process #3
         */
        $process1 = $this->createSampleProcess();
        $process1->start();
        $process2 = $this->createSampleProcess();
        $process2->start();
        $process3 = $this->createSampleProcess($process1->getId());
        $process3->stop();
        $process2->stop();
        $process1->stop();

        // Ensure that the three processes have been executed successfully
        $this->assertTrue($process1->isCompleted());
        $this->assertTrue($process2->isCompleted());
        $this->assertTrue($process3->isCompleted());

        // Ensure that processes #2 and #3 are children of #1
        $this->assertEquals($process1->getId(), $process2->getParentId());
        $this->assertEquals($process1->getId(), $process3->getParentId());
    }
}
