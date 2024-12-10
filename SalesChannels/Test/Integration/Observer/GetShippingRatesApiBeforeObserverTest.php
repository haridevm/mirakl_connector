<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Observer;

use Magento\Framework\Event;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\MMP\Front\Request\Shipping\GetShippingRatesRequest;
use Mirakl\SalesChannels\Observer\GetShippingRatesApiBeforeObserver;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group observer
 * @coversDefaultClass \Mirakl\SalesChannels\Observer\GetShippingRatesApiBeforeObserver
 * @covers ::__construct
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class GetShippingRatesApiBeforeObserverTest extends TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecuteWithChannelPricingDisabled()
    {
        $event = $this->objectManager->create(Event::class);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(GetShippingRatesApiBeforeObserver::class);
        $observer->execute($eventObserver);

        $this->assertFalse($this->config->isChannelPricingEnabled());
    }

    /**
     * @covers ::execute
     * @magentoDataFixture Mirakl_SalesChannels::Test/Integration/_fixtures/store_fr.php
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testExecute()
    {
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore('fr');

        $this->assertSame('fr', $storeManager->getStore()->getCode());

        $request = new GetShippingRatesRequest('FR', []);

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'request' => $request,
            ],
        ]);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(GetShippingRatesApiBeforeObserver::class);
        $observer->execute($eventObserver);

        $this->assertSame('FR', $request->getPricingChannelCode());
    }
}
