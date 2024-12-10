<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Process\Test\Integration\Model\Action\DelayableActionStub;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\DelayableTrait
 */
class DelayableTraitTest extends TestCase
{
    /**
     * @var DelayableActionStub
     */
    private $delayable;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $data = [
            'default_delay' => 2,
            'max_attempts' => 8,
        ];

        $this->delayable = new DelayableActionStub($data);
    }

    /**
     * @covers ::getDefaultDelay
     */
    public function testGetDefaultDelay()
    {
        $this->assertSame(2, $this->delayable->getDefaultDelay());
    }

    /**
     * @covers ::setDefaultDelay
     */
    public function testSetDefaultDelay()
    {
        $this->delayable->setDefaultDelay(4);
        $this->assertSame(4, $this->delayable->getDefaultDelay());
    }

    /**
     * @covers ::getMaxAttempts
     */
    public function testGetMaxAttempts()
    {
        $this->assertSame(8, $this->delayable->getMaxAttempts());
    }

    /**
     * @covers ::setMaxAttempts
     */
    public function testSetMaxAttempts()
    {
        $this->delayable->setMaxAttempts(12);
        $this->assertSame(12, $this->delayable->getMaxAttempts());
    }
}
