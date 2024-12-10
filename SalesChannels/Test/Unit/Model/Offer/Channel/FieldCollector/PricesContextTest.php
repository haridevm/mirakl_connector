<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Unit\Model\Offer\Channel\FieldCollector;

use Mirakl\Connector\Model\Offer\Import\Serializer\Tuples;
use Mirakl\Connector\Model\Offer\Import\Price\DiscountPriceProvider;
use Mirakl\Connector\Model\Offer\Import\Price\PriceRangesProvider;
use Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use PHPUnit\Framework\TestCase;

/**
 * @group sales_channels
 * @group OF54
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\PricesContext
 */
class PricesContextTest extends TestCase
{
    /**
     * @param string $channel
     * @param array  $additionalInfo
     * @param array  $expected
     * @dataProvider getTestCollectDataProvider
     * @covers ::collect
     */
    public function testCollect(string $channel, array $additionalInfo, array $expected)
    {
        /** @var ChannelOffer $offerMock */
        $offerMock = $this->getMockBuilder(ChannelOffer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $offerMock->setData('channel', $channel);
        $offerMock->setData('additional_info', $additionalInfo);

        $discountPriceProviderMock = $this->getMockBuilder(DiscountPriceProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $discountPriceProviderMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $priceRangesProviderMock = $this->getMockBuilder(PriceRangesProvider::class)
            ->onlyMethods([])
            ->getMock();

        $serializerMock = $this->getMockBuilder(Tuples::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $fieldCollector = new FieldCollector\PricesContext(
            $discountPriceProviderMock,
            $priceRangesProviderMock,
            $serializerMock
        );

        $values = $fieldCollector->collect($offerMock);

        $this->assertEquals($expected, $values);
    }

    /**
     * @return array
     */
    public function getTestCollectDataProvider(): array
    {
        return [
            [
                'INIT',
                [
                    'prices' => [
                        [
                            'context' => [
                                'channel_codes' => ['FR', 'INIT'],
                            ],
                            'discount_start_date' => '2023-08-15 00:00:00',
                            'discount_end_date'   => '2023-08-31 23:59:59',
                            'origin_price'  => 29,
                            'volume_prices' => [
                                [
                                    'quantity_threshold'  => 1,
                                    'unit_discount_price' => 28,
                                    'unit_origin_price'   => 29,
                                ],
                                [
                                    'quantity_threshold'  => 2,
                                    'unit_discount_price' => 26,
                                    'unit_origin_price'   => 27,
                                ],
                                [
                                    'quantity_threshold'  => 3,
                                    'unit_discount_price' => 24,
                                    'unit_origin_price'   => 25,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'price'               => 29,
                    'origin_price'        => 29,
                    'price_ranges'        => '1|29.00,2|27.00,3|25.00',
                    'discount_ranges'     => '1|28.00,2|26.00,3|24.00',
                    'discount_price'      => null,
                    'discount_start_date' => '2023-08-15 00:00:00',
                    'discount_end_date'   => '2023-08-31 23:59:59',
                ],
            ],
        ];
    }
}
