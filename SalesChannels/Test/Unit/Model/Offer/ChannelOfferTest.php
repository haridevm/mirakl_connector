<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Unit\Model\Offer;

use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use PHPUnit\Framework\TestCase;

class ChannelOfferTest extends TestCase
{
    /**
     * @param string|null $channel
     * @param string|null $channels
     * @param bool        $expected
     *
     * @dataProvider getTestIsAvailableDataProvider
     */
    public function testIsAvailable(?string $channel, ?string $channels, bool $expected)
    {
        /** @var ChannelOffer $offerMock */
        $offerMock = $this->getMockBuilder(ChannelOffer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $offerMock->setData([
            'channel'  => $channel,
            'channels' => $channels,
        ]);

        $this->assertSame($expected, $offerMock->isAvailable());
    }

    /**
     * @return array
     */
    public function getTestIsAvailableDataProvider(): array
    {
        return [
            ['FR', 'FR|INIT|MOBILE', true],
            ['INIT', 'FR|INIT|MOBILE', true],
            ['EN', 'FR|INIT|MOBILE', false],
            [null, 'FR|INIT|MOBILE', true],
            ['DE', null, false],
        ];
    }
}