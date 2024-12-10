<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Unit\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Execution\Validator\ConcurrencyValidator;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process\Collection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\ConcurrencyValidator
 * @covers ::__construct
 */
class ConcurrencyValidatorTest extends TestCase
{
    /**
     * @var ConcurrencyValidator
     */
    private $validator;

    /**
     * @var Collection|MockObject
     */
    private $processCollectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $processCollectionFactoryMock;

    /**
     * @var Process|MockObject
     */
    private $processMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->processCollectionMock = $this->createMock(Collection::class);

        $this->processCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->processCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->processCollectionMock);

        $this->processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode', 'getName'])
            ->getMock();

        $this->validator = new ConcurrencyValidator($this->processCollectionFactoryMock);
    }

    /**
     * @covers ::validate
     * @covers ::getRunningProcesses
     */
    public function testValidateSuccess()
    {
        $this->processCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $this->processMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn('foo');

        $this->validator->validate($this->processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     * @covers ::getRunningProcesses
     */
    public function testValidateFailed()
    {
        $this->processCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $this->expectException(AlreadyRunningException::class);
        $this->expectExceptionMessage('An identical process is already running.');

        $this->validator->validate($this->processMock);
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccessWithIgnoredProcessCodes()
    {
        $this->processMock->expects($this->once())
            ->method('getCode')
            ->willReturn('foo');

        $this->validator = new ConcurrencyValidator($this->processCollectionFactoryMock, ['foo']);

        $this->validator->validate($this->processMock);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }
}
