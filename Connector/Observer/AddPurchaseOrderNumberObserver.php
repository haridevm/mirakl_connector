<?php
namespace Mirakl\Connector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrder;

class AddPurchaseOrderNumberObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if ($payment && $payment->getPoNumber()) {
            /** @var CreateOrder $createOrder */
            $createOrder = $observer->getEvent()->getCreateOrder();
            $createOrder->setReferences([
                'order_reference_for_customer' => $payment->getPoNumber(),
            ]);
        }
    }
}
