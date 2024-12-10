<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Exception;

use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Exception\StopExecutionException
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class StopExecutionExceptionTest extends TestCase
{
    /**
     * @covers ::getStatus
     */
    public function testGetStatus()
    {
        $process = $this->createSampleProcess();

        $exception = new StopExecutionException($process, __('Stop me'), Process::STATUS_ERROR);

        $this->assertSame(Process::STATUS_ERROR, $exception->getStatus());
    }
}
