<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Offer;

use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\ChannelOffer
 */
class ChannelOfferTest extends TestCase
{
    /**
     * @var ChannelOffer
     */
    private $channelOffer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->channelOffer = $this->objectManager->create(ChannelOffer::class);
    }

    /**
     * @covers ::getChannel
     */
    public function testGetChannel()
    {
        $this->channelOffer->setData('channel', 'FR');

        $this->assertSame('FR', $this->channelOffer->getChannel());
    }

    /**
     * @covers ::getChannel
     */
    public function testGetChannelEmpty()
    {
        $this->assertNull($this->channelOffer->getChannel());
    }

    /**
     * @covers ::isAvailable
     */
    public function testIsAvailable()
    {
        $this->assertNull($this->channelOffer->getChannel());
        $this->assertTrue($this->channelOffer->isAvailable());

        $this->channelOffer->setData('channel', 'FR');

        $this->assertFalse($this->channelOffer->isAvailable());

        $this->channelOffer->setData('channels', 'FR|INIT|MOBILE');

        $this->assertTrue($this->channelOffer->isAvailable());
    }
}
