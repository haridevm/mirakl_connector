<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output\Formatter;

use Mirakl\Process\Model\Output\Formatter\NoTags;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Formatter\NoTags
 */
class NoTagsTest extends TestCase
{
    /**
     * @covers ::format
     */
    public function testFormat()
    {
        $formatter = $this->objectManager->create(NoTags::class);

        $this->assertSame('foo', $formatter->format('<strong>foo</strong>'));
    }
}
