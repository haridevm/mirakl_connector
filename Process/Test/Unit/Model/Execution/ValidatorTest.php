<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Unit\Model\Execution;

use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Execution\Validator;
use Mirakl\Process\Model\Process;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator
 * @covers ::__construct
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Process|MockObject
     */
    private $processMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->processMock = $this->createMock(Process::class);
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $validatorMock = $this->createMock(Validator\ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method('validate');

        $validator = new Validator([$validatorMock]);

        $validator->validate($this->processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccessWithEmptyValidators()
    {
        $validator = new Validator();

        $validator->validate($this->processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateFailed()
    {
        $validatorMock = $this->createMock(Validator\ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new ProcessException($this->processMock, __('Lorem ipsum dolor sit amet')));

        $validator = new Validator([$validatorMock]);

        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('Lorem ipsum dolor sit amet');

        $validator->validate($this->processMock);
    }
}
