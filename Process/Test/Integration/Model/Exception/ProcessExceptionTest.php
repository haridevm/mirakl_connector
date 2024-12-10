<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Exception;

use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Exception\ProcessException
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProcessExceptionTest extends TestCase
{
    /**
     * @covers ::getProcess
     */
    public function testGetProcess()
    {
        $process = $this->createSampleProcess();

        $exception = new ProcessException($process, __('An error occurred'));

        $this->assertSame($process, $exception->getProcess());
    }
}
