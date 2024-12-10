<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Unit\Model\Offer\Channel\FieldCollector;

use Mirakl\Connector\Model\Offer\Import\ChannelFields;
use Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use PHPUnit\Framework\TestCase;

/**
 * @group sales_channels
 * @group OF51
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\Prices
 */
class PricesTest extends TestCase
{
    /**
     * @param string $channel
     * @param array  $fields
     * @param array  $additionalInfo
     * @param array  $expected
     * @dataProvider getTestCollectDataProvider
     * @covers ::collect
     */
    public function testCollect(string $channel, array $fields, array $additionalInfo, array $expected)
    {
        /** @var ChannelOffer $offerMock */
        $offerMock = $this->getMockBuilder(ChannelOffer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $offerMock->setData('channel', $channel);
        $offerMock->setData('additional_info', $additionalInfo);

        $channelFieldsMock = $this->getMockBuilder(ChannelFields::class)
            ->disableOriginalConstructor()
            ->getMock();
        $channelFieldsMock->expects($this->once())
            ->method('get')
            ->willReturn($fields);

        $fieldCollector = new FieldCollector\Prices($channelFieldsMock);
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
                'FR',
                [
                    'price',
                    'origin_price',
                    'price_ranges',
                    'discount_price',
                    'discount_start_date',
                    'discount_end_date',
                    'discount_ranges',
                ],
                [
                    'price[channel=FR]'               => 100.18,
                    'origin_price[channel=FR]'        => 100.18,
                    'price_ranges[channel=FR]'        => '1|100.18,2|100.17,3|100.16',
                    'discount_price[channel=FR]'      => 99.18,
                    'discount_start_date[channel=FR]' => '2023-08-14 22:00:00',
                    'discount_end_date[channel=FR]'   => '2023-08-31 21:59:59',
                    'discount_ranges[channel=FR]'     => '1|99.18,2|99.17,3|99.16',
                    'price[channel=IT]'               => 120.04,
                    'origin_price[channel=IT]'        => 120.05,
                    'discount_price[channel=IT]'      => 120.04,
                ],
                [
                    'price'               => 100.18,
                    'origin_price'        => 100.18,
                    'price_ranges'        => '1|100.18,2|100.17,3|100.16',
                    'discount_price'      => 99.18,
                    'discount_start_date' => '2023-08-14 22:00:00',
                    'discount_end_date'   => '2023-08-31 21:59:59',
                    'discount_ranges'     => '1|99.18,2|99.17,3|99.16',
                ],
            ],
        ];
    }
}
