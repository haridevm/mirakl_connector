<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution;

use Mirakl\Process\Model\Exception\BadMethodException;
use Mirakl\Process\Model\Execution\Executor;
use Mirakl\Process\Model\Execution\Validator;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Executor
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ExecutorTest extends TestCase
{
    /**
     * @var Executor
     */
    private $executor;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->executor = new Executor($this->createMock(Validator::class));
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $process = $this->createSampleProcess();

        $this->executor->execute($process);

        $this->assertTrue($process->isProcessing());
        $this->assertStringContainsString('Running', $process->getOutput());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithInvalidMethod()
    {
        $this->expectException(BadMethodException::class);

        $process = $this->createSampleProcess();
        $process->setHelper('foo');
        $process->setMethod('bar');

        $this->executor->execute($process);
    }
}
