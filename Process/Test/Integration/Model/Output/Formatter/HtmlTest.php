<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output\Formatter;

use Mirakl\Process\Model\Output\Formatter\Html;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Formatter\Html
 * @covers ::__construct
 */
class HtmlTest extends TestCase
{
    /**
     * @covers ::format
     */
    public function testFormat()
    {
        $formatter = $this->objectManager->create(Html::class);

        $this->assertSame(
            '<span style="color: #ca1919;">foo</span>',
            $formatter->format('<error>foo</error>')
        );
    }
}
