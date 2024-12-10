<?php

declare(strict_types=1);

namespace Mirakl\Core\Observer\Sales\Invoice\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

class InvoiceItemSaveBeforeObserver implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice\Item $invoice */
        $invoice = $observer->getInvoiceItem();

        /** @var \Magento\Sales\Api\Data\InvoiceItemExtension $extensionAttributes */
        $extensionAttributes = $invoice->getExtensionAttributes();

        $invoice->addData([
            'mirakl_base_shipping_excl_tax'   => $extensionAttributes->getMiraklBaseShippingExclTax(),
            'mirakl_shipping_excl_tax'        => $extensionAttributes->getMiraklShippingExclTax(),
            'mirakl_base_shipping_incl_tax'   => $extensionAttributes->getMiraklBaseShippingInclTax(),
            'mirakl_shipping_incl_tax'        => $extensionAttributes->getMiraklShippingInclTax(),
            'mirakl_base_shipping_tax_amount' => $extensionAttributes->getMiraklBaseShippingTaxAmount(),
            'mirakl_shipping_tax_amount'      => $extensionAttributes->getMiraklShippingTaxAmount(),
        ]);
    }
}
