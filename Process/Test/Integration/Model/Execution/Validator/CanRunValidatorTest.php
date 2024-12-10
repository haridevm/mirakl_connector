<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\CannotRunException;
use Mirakl\Process\Model\Execution\Validator\CanRunValidator;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\CanRunValidator
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CanRunValidatorTest extends TestCase
{
    /**
     * @var CanRunValidator
     */
    private $validator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new CanRunValidator();
    }

    /**
     * @covers ::validate
     */
    public function testValidate()
    {
        $process = $this->createSampleProcess();

        $this->validator->validate($process);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithForceExecution()
    {
        $process = $this->createSampleProcess();
        $process->setStatus(Process::STATUS_COMPLETED);
        $process->setForceExecution(true);
        $process->output('Process is completed', true);

        $this->validator->validate($process);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithoutForceExecution()
    {
        $this->expectException(CannotRunException::class);

        $process = $this->createSampleProcess();
        $process->setStatus(Process::STATUS_COMPLETED);
        $process->output('Process is completed', true);

        $this->validator->validate($process);
    }
}
