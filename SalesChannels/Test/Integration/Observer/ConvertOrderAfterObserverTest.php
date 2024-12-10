<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Observer;

use Magento\Framework\Event;
use Magento\Sales\Model\Order;
use Mirakl\SalesChannels\Observer\ConvertOrderAfterObserver;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group observer
 * @coversDefaultClass \Mirakl\SalesChannels\Observer\ConvertOrderAfterObserver
 */
class ConvertOrderAfterObserverTest extends TestCase
{
    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $order = $this->objectManager->create(Order::class, [
            'data' => [
                'mirakl_channel_code' => 'FR',
            ],
        ]);
        $createOrder = $this->objectManager->create(Order::class);

        $this->assertSame('FR', $order->getMiraklChannelCode());
        $this->assertNull($createOrder->getMiraklChannelCode());

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'order'        => $order,
                'create_order' => $createOrder,
            ],
        ]);

        $eventObserver = $this->objectManager->create(Event\Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(ConvertOrderAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertSame($order->getMiraklChannelCode(), $createOrder->getChannelCode());
        $this->assertSame('FR', $createOrder->getChannelCode());
    }
}
