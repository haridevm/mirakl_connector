<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\AbstractAction
 */
class AbstractActionTest extends TestCase
{
    /**
     * @var AbstractAction
     */
    private $action;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->action = new ActionStub();
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertSame('Simple action stub', $this->action->getName());
    }

    /**
     * @covers ::addParams
     */
    public function testAddParams()
    {
        $this->action->addParams(['foo' => 'bar']);
        $this->assertSame('bar', $this->action->getData('foo'));
    }

    /**
     * @covers ::getParams
     */
    public function testGetParams()
    {
        $this->assertSame([], $this->action->getParams());
    }

    /**
     * @covers ::setParams
     */
    public function testSetParams()
    {
        $this->action->setParams(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $this->action->getParams());
    }
}
