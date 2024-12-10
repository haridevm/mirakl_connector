<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Execution\Validator\AlreadyRunningValidator;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\AlreadyRunningValidator
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AlreadyRunningValidatorTest extends TestCase
{
    /**
     * @var AlreadyRunningValidator
     */
    private $validator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new AlreadyRunningValidator();
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
    public function testValidateWithException()
    {
        $this->expectException(AlreadyRunningException::class);

        $process = $this->createSampleProcess();
        $process->setStatus(Process::STATUS_PROCESSING);

        $this->validator->validate($process);
    }
}
