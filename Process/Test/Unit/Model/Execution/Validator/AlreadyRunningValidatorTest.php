<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Unit\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Execution\Validator\AlreadyRunningValidator;
use Mirakl\Process\Model\Process;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\AlreadyRunningValidator
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
    protected function setUp(): void
    {
        $this->validator = new AlreadyRunningValidator();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isProcessing'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isProcessing')
            ->willReturn(false);

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
            ->onlyMethods(['isProcessing'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isProcessing')
            ->willReturn(true);

        $this->expectException(AlreadyRunningException::class);
        $this->expectExceptionMessage('Process is already running. Please try again later.');

        $this->validator->validate($processMock);
    }
}
