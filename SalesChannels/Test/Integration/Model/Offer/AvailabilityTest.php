<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Offer;

use Mirakl\Connector\Model\Offer;
use Mirakl\SalesChannels\Model\Offer\Availability;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\Availability
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class AvailabilityTest extends TestCase
{
    /**
     * @var Availability
     */
    private $availability;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->availability = $this->objectManager->create(Availability::class);
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithoutChannelOffer()
    {
        $offer = $this->objectManager->create(Offer::class);
        $store = $this->storeRepository->get('default');

        $this->assertTrue($this->availability->validate($offer, $store));
    }

    /**
     * @covers ::validate
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testValidate()
    {
        $channelOffer = $this->objectManager->create(ChannelOffer::class);
        $channelOffer->setData('channel', 'INIT');
        $channelOffer->setData('channels', 'FR|INIT|MOBILE');

        $store = $this->storeRepository->get('default');

        $this->assertTrue($this->availability->validate($channelOffer, $store));

        $channelOffer->setData('channel', 'UK');

        $this->assertFalse($this->availability->validate($channelOffer, $store));
    }
}
