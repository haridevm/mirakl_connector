<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution;

use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Execution\Validator;
use Mirakl\Process\Model\Execution\Validator\ValidatorInterface;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator
 * @covers ::__construct
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ValidatorTest extends TestCase
{
    /**
     * @covers ::validate
     */
    public function testValidate()
    {
        $validators = [
            $this->createMock(ValidatorInterface::class),
        ];

        $validator = new Validator($validators);

        $validator->validate($this->createSampleProcess());

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithException()
    {
        $this->expectException(ProcessException::class);

        $process = $this->createSampleProcess();

        $exception = new ProcessException($process, __('Error'));

        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method('validate')
            ->willThrowException($exception);

        $validator = new Validator([$validatorMock]);

        $validator->validate($process);
    }
}
