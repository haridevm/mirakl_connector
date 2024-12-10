<?php

declare(strict_types=1);

namespace Mirakl\Api\Test\Unit\Model\Client\Authentication\Token\Decorator;

use Mirakl\Api\Model\Client\Authentication\Token\Decorator\BearerDecorator;
use PHPUnit\Framework\TestCase;

/**
 * @group api
 * @group model
 * @group auth
 * @coversDefaultClass \Mirakl\Api\Model\Client\Authentication\Token\Decorator\BearerDecorator
 */
class BearerDecoratorTest extends TestCase
{
    /**
     * @var BearerDecorator
     */
    protected $bearerDecorator;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->bearerDecorator = new BearerDecorator();
    }

    /**
     * @param string $input
     * @param string $expected
     * @dataProvider getTestDecorateDataProvider
     * @covers ::decorate
     */
    public function testDecorate(string $input, string $expected)
    {
        $this->assertSame($expected, $this->bearerDecorator->decorate($input));
    }

    /**
     * @return array
     */
    public function getTestDecorateDataProvider(): array
    {
        return [
            ['foo', 'Bearer foo'],
            ['Bearer foobar', 'Bearer foobar'],
            ['bearer 123', 'bearer 123'],
            ['BEARER 456', 'BEARER 456'],
        ];
    }
}
