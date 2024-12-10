<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Observer;

use Magento\Framework\Event;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use Mirakl\SalesChannels\Observer\OfferCollectionLoadAfterObserver;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group observer
 * @coversDefaultClass \Mirakl\SalesChannels\Observer\OfferCollectionLoadAfterObserver
 * @covers ::__construct
 */
class OfferCollectionLoadAfterObserverTest extends TestCase
{
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
        $this->offerCollectionFactory = $this->objectManager->create(OfferCollectionFactory::class);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithoutChannelMapping()
    {
        $this->importSampleOffers();

        /** @var OfferCollection $offerCollection */
        $offerCollection = $this->offerCollectionFactory->create();

        $offer = $offerCollection->getFirstItem();

        $this->assertNotInstanceOf(ChannelOffer::class, $offer);
        $this->assertNull($offer->getChannel());
        $this->assertEquals('2220', $offer->getId());
        $this->assertEquals(20.89, $offer->getPrice());
        $this->assertEquals(0.00, $offer->getDiscountPrice());

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'offer_collection' => $offerCollection,
            ],
        ]);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(OfferCollectionLoadAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertEquals(20.89, $offer->getPrice());
        $this->assertEquals(0.00, $offer->getDiscountPrice());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithChannelMapping()
    {
        $this->importSampleOffers();

        /** @var OfferCollection $offerCollection */
        $offerCollection = $this->offerCollectionFactory->create();
        $offerCollection->setItemObjectClass(ChannelOffer::class);

        $channelOffer = $offerCollection->getFirstItem();
        $channelOffer->setData('channel', 'FR');

        $this->assertInstanceOf(ChannelOffer::class, $channelOffer);
        $this->assertSame('FR', $channelOffer->getChannel());
        $this->assertEquals('2220', $channelOffer->getId());
        $this->assertEquals(20.89, $channelOffer->getPrice());
        $this->assertEquals(0.00, $channelOffer->getDiscountPrice());

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'offer_collection' => $offerCollection,
            ],
        ]);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(OfferCollectionLoadAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertEquals(21.00, $channelOffer->getOriginPrice());
        $this->assertEquals(20.79, $channelOffer->getPrice());
    }
}
