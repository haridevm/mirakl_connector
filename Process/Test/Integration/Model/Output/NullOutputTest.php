<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output;

use Mirakl\Process\Model\Output\NullOutput;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\NullOutput
 */
class NullOutputTest extends TestCase
{
    /**
     * @var NullOutput
     */
    private $output;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->output = $this->objectManager->create(NullOutput::class);
    }

    /**
     * @covers ::display
     */
    public function testDisplay()
    {
        $this->assertSame($this->output, $this->output->display('test'));
    }
}
