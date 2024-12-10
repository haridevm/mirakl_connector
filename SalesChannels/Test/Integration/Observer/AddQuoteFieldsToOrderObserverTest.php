<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Observer;

use Magento\Framework\Event;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Mirakl\SalesChannels\Observer\AddQuoteFieldsToOrderObserver;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group observer
 * @coversDefaultClass \Mirakl\SalesChannels\Observer\AddQuoteFieldsToOrderObserver
 */
class AddQuoteFieldsToOrderObserverTest extends TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $quote = $this->objectManager->create(Quote::class, [
            'data' => [
                'mirakl_channel_code' => 'FR',
            ],
        ]);
        $order = $this->objectManager->create(Order::class);

        $this->assertSame('FR', $quote->getMiraklChannelCode());
        $this->assertNull($order->getMiraklChannelCode());

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'quote' => $quote,
                'order' => $order,
            ],
        ]);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(AddQuoteFieldsToOrderObserver::class);
        $observer->execute($eventObserver);

        $this->assertSame($quote->getMiraklChannelCode(), $order->getMiraklChannelCode());
        $this->assertSame('FR', $order->getMiraklChannelCode());
    }
}
