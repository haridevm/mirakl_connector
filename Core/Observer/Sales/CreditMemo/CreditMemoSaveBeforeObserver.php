<?php
namespace Mirakl\Core\Observer\Sales\CreditMemo;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class CreditMemoSaveBeforeObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditMemo */
        $creditMemo = $observer->getCreditmemo();

        /** @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $creditMemo->getExtensionAttributes();

        $creditMemo->addData([
            'mirakl_base_shipping_excl_tax'   => $extensionAttributes->getMiraklBaseShippingExclTax(),
            'mirakl_shipping_excl_tax'        => $extensionAttributes->getMiraklShippingExclTax(),
            'mirakl_base_shipping_incl_tax'   => $extensionAttributes->getMiraklBaseShippingInclTax(),
            'mirakl_shipping_incl_tax'        => $extensionAttributes->getMiraklShippingInclTax(),
        ]);
    }
}