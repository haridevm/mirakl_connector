<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output\Formatter;

use Mirakl\Process\Model\Output\Formatter\Factory;
use Mirakl\Process\Model\Output\Formatter\NoTags;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Formatter\Factory
 * @covers ::__construct
 */
class FactoryTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $factory = $this->objectManager->create(Factory::class);

        $this->assertInstanceOf(NoTags::class, $factory->create('no_tags'));
    }

    /**
     * @covers ::create
     */
    public function testCreateWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find output formatter for type unknown');

        $factory = $this->objectManager->create(Factory::class);

        $factory->create('unknown');
    }
}
