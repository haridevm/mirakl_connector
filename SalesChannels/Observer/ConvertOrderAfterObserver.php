<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrder;

class ConvertOrderAfterObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($channel = $order->getMiraklChannelCode()) {
            /** @var CreateOrder $createOrder */
            $createOrder = $observer->getEvent()->getCreateOrder();
            $createOrder->setChannelCode($channel);
        }
    }
}
