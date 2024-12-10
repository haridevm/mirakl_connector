<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output;

use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Process\Model\Output\AbstractOutput;
use Mirakl\Process\Model\Output\Formatter\Factory as FormatterFactory;
use Mirakl\Process\Model\Output\NullOutput;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\AbstractOutput
 * @covers ::__construct
 */
class AbstractOutputTest extends TestCase
{
    /**
     * @var AbstractOutput|MockObject
     */
    private $outputMock;

    /**
     * @var string
     */
    private $display = '';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->outputMock = $this->getMockBuilder(AbstractOutput::class)
            ->setConstructorArgs([
                $this->objectManager->create(CoreHelper::class),
                $this->objectManager->create(Process::class),
                $this->objectManager->create(LoggerInterface::class),
                $this->objectManager->create(FormatterFactory::class),
            ])
            ->getMockForAbstractClass();

        $this->outputMock->expects($this->any())
            ->method('display')
            ->willReturnCallback(function ($str) {
                $this->display .= $str;
                return $this->outputMock;
            });

        $this->display = '';
    }

    /**
     * @covers ::close
     */
    public function testClose()
    {
        $this->assertSame($this->outputMock->close(), $this->outputMock);
    }

    /**
     * @covers ::getType
     */
    public function testGetType()
    {
        $this->assertStringContainsString('abstractoutput', $this->outputMock->getType());
    }

    /**
     * @covers ::getType
     */
    public function testGetTypeWithNamespace()
    {
        $output = $this->objectManager->create(NullOutput::class);
        $this->assertSame('nulloutput', $output->getType());
    }
}
