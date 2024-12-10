<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output;

use Mirakl\Process\Model\Output\Cli;
use Mirakl\Process\Model\Output\Factory;
use Mirakl\Process\Model\Output\NullOutput;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $factory = $this->objectManager->create(Factory::class);
        $processMock = $this->createMock(Process::class);

        $this->assertInstanceOf(Cli::class, $factory->create('cli', $processMock));
    }

    /**
     * @covers ::create
     */
    public function testCreateWithUnknownType()
    {
        $factory = $this->objectManager->create(Factory::class);
        $processMock = $this->createMock(Process::class);

        $this->assertInstanceOf(NullOutput::class, $factory->create('unknown', $processMock));
    }
}
