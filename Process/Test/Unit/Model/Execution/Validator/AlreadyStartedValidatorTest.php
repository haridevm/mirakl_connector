<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Unit\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyStartedException;
use Mirakl\Process\Model\Execution\Validator\AlreadyStartedValidator;
use Mirakl\Process\Model\Process;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\AlreadyStartedValidator
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
    protected function setUp(): void
    {
        $this->validator = new AlreadyStartedValidator();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isStarted'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isStarted')
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
            ->onlyMethods(['isStarted'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('isStarted')
            ->willReturn(true);

        $this->expectException(AlreadyStartedException::class);
        $this->expectExceptionMessage('Cannot start a process that is already started.');

        $this->validator->validate($processMock);
    }
}
