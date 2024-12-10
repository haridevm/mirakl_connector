<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution;

use Mirakl\Process\Model\Execution\Failure;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Failure
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class FailureTest extends TestCase
{
    /**
     * @covers ::propagate
     * @covers ::stopParents
     * @covers ::cancelChildren
     */
    public function testPropagate()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         * process #1
         *  |_ process #2
         *    |_ process #3
         *    |_ process #4
         *      |_ process #5
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());
        $process3 = $this->createSampleProcess($process2->getId());
        $process4 = $this->createSampleProcess($process2->getId());
        $process5 = $this->createSampleProcess($process4->getId());

        // Simulate process #2 failure, parent and children must be modified
        $failure = new Failure();
        $failure->propagate($process2, Process::STATUS_ERROR);

        // Reload processes that have changed in db
        $process1 = $this->getProcessById($process1->getId());
        $process3 = $this->getProcessById($process3->getId());
        $process4 = $this->getProcessById($process4->getId());
        $process5 = $this->getProcessById($process5->getId());

        $this->assertTrue($process1->isError());
        $this->assertTrue($process3->isCancelled());
        $this->assertTrue($process4->isCancelled());
        $this->assertTrue($process5->isCancelled());
    }
}
