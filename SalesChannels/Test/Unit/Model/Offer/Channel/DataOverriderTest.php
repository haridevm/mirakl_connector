<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Unit\Model\Offer\Channel;

use Mirakl\SalesChannels\Model\Offer\Channel\DataOverrider;
use Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\Prices;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use PHPUnit\Framework\TestCase;

class DataOverriderTest extends TestCase
{
    /**
     * @param array $offer
     * @param array $override
     * @param array $expected
     *
     * @dataProvider getTestDataOverriderDataProvider
     */
    public function testDataOverrider(array $offer, array $override, array $expected)
    {
        /** @var ChannelOffer $offerMock */
        $offerMock = $this->getMockBuilder(ChannelOffer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $offerMock->setData($offer);

        $fieldCollectorMock = $this->getMockBuilder(Prices::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldCollectorMock->expects($this->once())
            ->method('collect')
            ->willReturn($override);

        $dataOverrider = new DataOverrider([$fieldCollectorMock]);
        $dataOverrider->override($offerMock);

        $this->assertEquals($expected, $offerMock->getData());
    }

    /**
     * @return array
     */
    public function getTestDataOverriderDataProvider(): array
    {
        return [
            [ // Offer #1: test prices override
                [ // Offer initial data
                    'offer_id'       => '1',
                    'price'          => 19.90,
                    'origin_price'   => 19.90,
                    'discount_price' => 17.50,
                ],
                [ // Override data
                    'price'               => 18,
                    'origin_price'        => 30.55,
                    'discount_price'      => 18,
                    'discount_start_date' => '2023-08-14 23:42:18',
                ],
                [ // Expected offer data
                    'offer_id'            => '1',
                    'price'               => 18,
                    'origin_price'        => 30.55,
                    'discount_price'      => 18,
                    'discount_start_date' => '2023-08-14 23:42:18',
                ],
            ],
            [ // Offer #2: test price/discount ranges override
                [ // Offer initial data
                    'offer_id'        => '2',
                    'price'           => 29.00,
                    'origin_price'    => 34.29,
                    'discount_price'  => 28.00,
                    'price_ranges'    => '0000-00-00 00:00:00',
                    'discount_ranges' => '0000-00-00 00:00:00',
                ],
                [ // Override data
                    'price_ranges'    => '1|29.00,2|27.00,3|25.00',
                    'discount_ranges' => '1|28.00,2|26.00,3|24.00',
                ],
                [ // Expected offer data
                    'offer_id'        => '2',
                    'price'           => 29.00,
                    'origin_price'    => 34.29,
                    'discount_price'  => 28.00,
                    'price_ranges'    => '1|29.00,2|27.00,3|25.00',
                    'discount_ranges' => '1|28.00,2|26.00,3|24.00',
                ],
            ],
        ];
    }
}