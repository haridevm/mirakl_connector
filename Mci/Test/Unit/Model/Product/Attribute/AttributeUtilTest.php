<?php

declare(strict_types=1);

namespace Mirakl\Mci\Test\Unit\Model\Product\Attribute;

use Mirakl\Mci\Model\Product\Attribute\AttributeUtil;
use PHPUnit\Framework\TestCase;

class AttributeUtilTest extends TestCase
{
    /**
     * @param string      $attrCode
     * @param bool        $isLocalized
     * @param string|null $locale
     * @param string      $code
     *
     * @dataProvider getTestParseDataProvider
     */
    public function testParse($attrCode, $isLocalized, $locale, $code)
    {
        $attrInfo = AttributeUtil::parse($attrCode);

        $this->assertSame($isLocalized, $attrInfo->isLocalized());
        $this->assertSame($locale, $attrInfo->getLocale());
        $this->assertSame($code, $attrInfo->getCode());
    }

    /**
     * @return array
     */
    public function getTestParseDataProvider()
    {
        return [
            ['foo-bar', false, null, 'foo-bar'],
            ['foo-bar-baz', false, null, 'foo-bar-baz'],
            ['foo-fr_FR-en_US', true, 'en_US', 'foo-fr_FR'],
            ['foo-fr_FR', true, 'fr_FR', 'foo'],
            ['foo_bar-fr_FR', true, 'fr_FR', 'foo_bar'],
            ['foo-bar-en_US', true, 'en_US', 'foo-bar'],
        ];
    }
}
