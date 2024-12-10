<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution;

use Mirakl\Process\Test\Integration\TestCase;
use Mirakl\Process\Model\Execution\Stack;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Stack
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class StackTest extends TestCase
{
    /**
     * @covers ::add
     * @covers ::current
     */
    public function testAdd()
    {
        $stack = new Stack();

        $process = $this->createSampleProcess();

        $stack->add($process);

        $this->assertSame($process, $stack->current());
    }

    /**
     * @covers ::add
     */
    public function testAddWithNoId()
    {
        $stack = new Stack();

        $process = $this->createSampleProcess();
        $process->setId(null);

        $stack->add($process);

        $this->assertNull($stack->current());
    }

    /**
     * @covers ::add
     */
    public function testAddWithParent()
    {
        $stack = new Stack();

        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess();

        $stack->add($process1);
        $stack->add($process2);

        $this->assertSame($process1, $process2->getParent());
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        $stack = new Stack();

        $process = $this->createSampleProcess();

        $stack->add($process);
        $stack->remove($process);

        $this->assertNull($stack->current());
    }
}
