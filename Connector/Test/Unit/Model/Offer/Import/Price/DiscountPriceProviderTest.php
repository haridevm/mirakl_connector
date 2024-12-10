<?php

declare(strict_types=1);

namespace Mirakl\Connector\Test\Unit\Model\Offer\Import\Price;

use Mirakl\Connector\Model\Offer\Import\Price\DiscountPriceProvider;
use Mirakl\Connector\Model\Offer\Import\Price\PriceValidator;
use Mirakl\Core\Test\TestCase;

class DiscountPriceProviderTest extends TestCase
{
    /**
     * @param array      $price
     * @param float|null $expected
     *
     * @dataProvider getTestGetDataProvider
     */
    public function testGet(array $price, ?float $expected)
    {
        $priceValidatorMock = $this->getMockBuilder(PriceValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceValidatorMock->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        $discountPriceProvider = new DiscountPriceProvider($priceValidatorMock);

        $this->assertSame($expected, $discountPriceProvider->get($price));
    }

    /**
     * @return array
     */
    public function getTestGetDataProvider(): array
    {
        return [
            [
                [],
                null // expected
            ],
            [
                [
                    'volume_prices' => [
                        [
                            'quantity_threshold'  => 1,
                            'unit_discount_price' => 45.72,
                            'unit_origin_price'   => 46.90,
                        ],
                        [
                            'quantity_threshold'  => 2,
                            'unit_discount_price' => 45.19,
                            'unit_origin_price'   => 46.58,
                        ],
                        [
                            'quantity_threshold'  => 3,
                            'unit_discount_price' => 44.80,
                            'unit_origin_price'   => 46.07,
                        ],
                    ],
                ],
                45.72 // expected
            ]
        ];
    }
}
