<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Indexer\Offer\Collection\Customizer;

use Mirakl\Connector\Model\Offer;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\SalesChannels\Model\Indexer\Offer\Collection\Customizer\SalesChannels;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Indexer\Offer\Collection\Customizer\SalesChannels
 * @covers ::__construct
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class SalesChannelsTest extends TestCase
{
    /**
     * @var SalesChannels
     */
    private $customizer;

    /**
     * @var OfferCollectionFactory
     */
    private $offerCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customizer = $this->objectManager->create(SalesChannels::class);
        $this->offerCollectionFactory = $this->objectManager->create(OfferCollectionFactory::class);
    }

    /**
     * @covers ::customize
     */
    public function testCustomizeWithChannelPricingDisabled()
    {
        /** @var OfferCollection $offerCollection */
        $offerCollection = $this->offerCollectionFactory->create();
        $store = $this->storeRepository->get('default');

        $this->customizer->customize($offerCollection, $store);

        $this->assertFalse($this->config->isChannelPricingEnabled());
    }

    /**
     * @covers ::customize
     * @magentoDataFixture Mirakl_SalesChannels::Test/Integration/_fixtures/store_fr.php
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testCustomize()
    {
        $this->importSampleOffers();

        /** @var OfferCollection $offerCollection */
        $offerCollection = $this->offerCollectionFactory->create();
        $store = $this->storeRepository->get('fr');

        $this->assertCount(1, $offerCollection->getItems());
        $this->assertSame(Offer::class, $offerCollection->getItemObjectClass());

        $this->customizer->customize($offerCollection, $store);

        $this->assertSame(ChannelOffer::class, $offerCollection->getItemObjectClass());
    }
}
