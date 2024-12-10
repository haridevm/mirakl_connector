<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Unit\Model\Product\Import\Data\Parser;

use Mirakl\Mcm\Helper\Config;
use Mirakl\Mcm\Model\Product\Import\Data\Parser\MultiValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultiValueTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    protected $mcmConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mcmConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $str
     * @param array  $expected
     *
     * @dataProvider getTestParseMultiValueAttributeDataProviderForSyncImport
     */
    public function testParseMultiValueAttributeForSyncImport(string $str, array $expected)
    {
        // MCM sync import enabled
        $this->mcmConfigMock->expects($this->any())
            ->method('isMcmEnabled')
            ->willReturn(true);
        $this->mcmConfigMock->expects($this->any())
            ->method('isAsyncMcmEnabled')
            ->willReturn(false);

        $parser = new MultiValue();
        $this->assertSame($expected, $parser->parse($str));
    }

    /**
     * @param array $value
     * @param array $expected
     *
     * @dataProvider getTestParseMultiValueAttributeDataProviderForAsyncImport
     */
    public function testParseMultiValueAttributeForAsyncImport(array $value, array $expected)
    {
        // MCM async import enabled
        $this->mcmConfigMock->expects($this->any())
            ->method('isMcmEnabled')
            ->willReturn(false);
        $this->mcmConfigMock->expects($this->any())
            ->method('isAsyncMcmEnabled')
            ->willReturn(true);

        $parser = new MultiValue();
        $this->assertSame($expected, $parser->parse($value));
    }

    /**
     * @return array
     */
    public function getTestParseMultiValueAttributeDataProviderForSyncImport(): array
    {
        return [
            ['', []],
            ['100', ['100']],
            ['1,2,3,4', ['1', '2', '3', '4']],
            ['01;000;0123;9', ['01', '000', '0123', '9']],
            ['120#367#8291#91#10', ['120', '367', '8291', '91', '10']],
            ['0|1|2|3|4|5', ['0', '1', '2', '3', '4', '5']],
        ];
    }

    /**
     * @return array
     */
    public function getTestParseMultiValueAttributeDataProviderForAsyncImport(): array
    {
        return [
            [[], []],
            [['100'], ['100']],
            [['1', '2', '3', '4'], ['1', '2', '3', '4']],
        ];
    }
}
