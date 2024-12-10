<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\ActionList;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\ActionList
 * @covers ::__construct
 */
class ActionListTest extends TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $actionList = new ActionList([new ActionStub()]);

        $this->assertInstanceOf(ActionStub::class, $actionList->get()->current());
    }

    /**
     * @covers ::get
     */
    public function testGetEmpty()
    {
        $actionList = new ActionList();

        $this->assertNull($actionList->get()->current());
    }

    /**
     * @covers ::areParamsChainable
     */
    public function testAreParamsChainable()
    {
        $actionList = new ActionList();

        $this->assertTrue($actionList->areParamsChainable());
    }
}
