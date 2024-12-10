<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Unit\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\CannotRunException;
use Mirakl\Process\Model\Execution\Validator\CanRunValidator;
use Mirakl\Process\Model\Process;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\CanRunValidator
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
    protected function setUp(): void
    {
        $this->validator = new CanRunValidator();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccessWithPendingProcess()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPending'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isPending')
            ->willReturn(true);

        $this->validator->validate($processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccessWithForceExecution()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPending'])
            ->addMethods(['getForceExecution'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isPending')
            ->willReturn(false);

        $processMock->expects($this->once())
            ->method('getForceExecution')
            ->willReturn(true);

        $this->validator->validate($processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     */
    public function testValidateFailed()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isPending'])
            ->addMethods(['getForceExecution'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isPending')
            ->willReturn(false);

        $processMock->expects($this->once())
            ->method('getForceExecution')
            ->willReturn(false);

        $this->expectException(CannotRunException::class);
        $this->expectExceptionMessage('Cannot run a process that is not in pending status.');

        $this->validator->validate($processMock);
    }
}
