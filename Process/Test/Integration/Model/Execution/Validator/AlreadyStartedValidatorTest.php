<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyStartedException;
use Mirakl\Process\Model\Execution\Validator\AlreadyStartedValidator;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\AlreadyStartedValidator
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AlreadyStartedValidatorTest extends TestCase
{
    /**
     * @var AlreadyStartedValidator
     */
    private $validator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new AlreadyStartedValidator();
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
        $this->expectException(AlreadyStartedException::class);

        $process = $this->createSampleProcess();
        $process->start();

        $this->validator->validate($process);
    }
}
