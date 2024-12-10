<?php
namespace Mirakl\Core\Plugin\Model\Order\CreditMemo;

use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo\RefundOperation;

class RefundOperationPlugin
{
    /**
     * @param   RefundOperation     $subject
     * @param   OrderInterface      $order
     * @param   CreditmemoInterface $creditMemo
     * @return  OrderInterface
     */
    public function afterExecute(RefundOperation $subject, OrderInterface $order, CreditmemoInterface $creditMemo)
    {
        /** @var CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $creditMemo->getExtensionAttributes();

        if ($extensionAttributes->getMiraklBaseShippingExclTax()) {
            /** @var Order $order */
            $order->setMiraklShippingRefunded(
                $order->getMiraklShippingRefunded()
                + $extensionAttributes->getMiraklShippingExclTax()
            );
            $order->setMiraklBaseShippingRefunded(
                $order->getMiraklBaseShippingRefunded()
                + $extensionAttributes->getMiraklBaseShippingExclTax()
            );
            $order->setMiraklShippingTaxRefunded(
                $order->getMiraklShippingTaxRefunded()
                + $extensionAttributes->getMiraklShippingInclTax()
                - $extensionAttributes->getMiraklShippingExclTax()
            );
            $order->setMiraklBaseShippingTaxRefunded(
                $order->getMiraklBaseShippingTaxRefunded()
                + $extensionAttributes->getMiraklBaseShippingInclTax()
                - $extensionAttributes->getMiraklBaseShippingExclTax()
            );
        }

        return $order;
    }
}