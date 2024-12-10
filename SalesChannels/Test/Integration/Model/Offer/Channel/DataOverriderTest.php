<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Offer\Channel;

use Mirakl\SalesChannels\Model\Offer\Channel\DataOverrider;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\Channel\DataOverrider
 * @covers ::__construct
 */
class DataOverriderTest extends TestCase
{
    /**
     * @var DataOverrider
     */
    private $dataOverrider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dataOverrider = $this->objectManager->create(DataOverrider::class);
    }

    /**
     * @covers ::override
     */
    public function testOverride()
    {
        $this->importSampleOffers();

        /** @var ChannelOffer $channelOffer */
        $channelOffer = $this->objectManager->create(ChannelOffer::class);
        $channelOffer->load(2220); // @phpstan-ignore-line

        $this->assertNull($channelOffer->getChannel());
        $this->assertEquals('2220', $channelOffer->getId());
        $this->assertEquals(20.89, $channelOffer->getPrice());
        $this->assertEquals(0.00, $channelOffer->getDiscountPrice());

        // There is no price override for the channel INIT, the price should not change
        $channelOffer->setData('channel', 'INIT');

        $this->dataOverrider->override($channelOffer);

        $this->assertSame('INIT', $channelOffer->getChannel());
        $this->assertEquals(20.89, $channelOffer->getPrice());
        $this->assertEquals(0.00, $channelOffer->getDiscountPrice());

        // Price should be different for channel FR
        $channelOffer->setData('channel', 'FR');

        $this->dataOverrider->override($channelOffer);

        $this->assertSame('FR', $channelOffer->getChannel());
        $this->assertEquals(21.00, $channelOffer->getOriginPrice());
        $this->assertEquals(20.79, $channelOffer->getPrice());
    }
}
