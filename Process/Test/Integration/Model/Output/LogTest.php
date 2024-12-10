<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output;

use Mirakl\Process\Model\Output;
use Mirakl\Process\Test\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Log
 */
class LogTest extends TestCase
{
    /**
     * @var Output\Log
     */
    private $output;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->output = $this->objectManager->create(Output\Log::class, [
            'logger' => $this->loggerMock,
        ]);
    }

    /**
     * @covers ::display
     * @covers \Mirakl\Process\Model\Output\AbstractOutput::format
     * @covers \Mirakl\Process\Model\Output\AbstractOutput::getFormatter
     */
    public function testDisplay()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('This is a test message');

        $this->output->display('This is a test message');
    }
}
