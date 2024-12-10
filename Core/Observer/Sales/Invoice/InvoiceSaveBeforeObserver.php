<?php

declare(strict_types=1);

namespace Mirakl\Core\Observer\Sales\Invoice;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

class InvoiceSaveBeforeObserver implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getInvoice();

        /** @var \Magento\Sales\Api\Data\InvoiceExtension $extensionAttributes */
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
