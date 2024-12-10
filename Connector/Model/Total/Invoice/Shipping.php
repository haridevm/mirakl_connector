<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Mirakl\Connector\Model\Order\InvoiceManagement;

class Shipping extends AbstractTotal
{
    /**
     * @var InvoiceManagement
     */
    protected $invoiceManagement;

    /**
     * @param InvoiceManagement $invoiceManagement
     * @param array             $data
     */
    public function __construct(InvoiceManagement $invoiceManagement, array $data = [])
    {
        parent::__construct($data);
        $this->invoiceManagement = $invoiceManagement;
    }

    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        if (!$invoice->getOrder()->getMiraklShippingExclTax()) {
            return $this;
        }

        $miraklShippingExclTax       = 0;
        $miraklBaseShippingExclTax   = 0;
        $miraklShippingInclTax       = 0;
        $miraklBaseShippingInclTax   = 0;
        $miraklShippingTaxAmount     = 0;
        $miraklBaseShippingTaxAmount = 0;

        /** @var Invoice\Item $item */
        foreach ($invoice->getItems() as $item) {
            $orderItem = $item->getOrderItem();

            if (
                $item->getQty() <= 0
                || $orderItem->isDummy()
                || !$orderItem->getMiraklOfferId()
                || !$this->invoiceManagement->canIncludeMiraklOfferShipping($invoice, $orderItem->getMiraklOfferId())
            ) {
                continue;
            }

            $itemShippingTaxAmount = $orderItem->getMiraklShippingTaxAmount()
                + $orderItem->getMiraklCustomShippingTaxAmount();
            $itemBaseShippingTaxAmount = $orderItem->getMiraklBaseShippingTaxAmount()
                + $orderItem->getMiraklBaseCustomShippingTaxAmount();

            $miraklShippingExclTax       += $orderItem->getMiraklShippingExclTax();
            $miraklBaseShippingExclTax   += $orderItem->getMiraklBaseShippingExclTax();
            $miraklShippingInclTax       += $orderItem->getMiraklShippingInclTax();
            $miraklBaseShippingInclTax   += $orderItem->getMiraklBaseShippingInclTax();
            $miraklShippingTaxAmount     += $itemShippingTaxAmount;
            $miraklBaseShippingTaxAmount += $itemBaseShippingTaxAmount;

            /** @var \Magento\Sales\Api\Data\InvoiceItemExtension $extensionAttributes */
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes->setMiraklShippingExclTax($orderItem->getMiraklShippingExclTax());
            $extensionAttributes->setMiraklBaseShippingExclTax($orderItem->getMiraklBaseShippingExclTax());
            $extensionAttributes->setMiraklShippingInclTax($orderItem->getMiraklShippingInclTax());
            $extensionAttributes->setMiraklBaseShippingInclTax($orderItem->getMiraklBaseShippingInclTax());
            $extensionAttributes->setMiraklShippingTaxAmount($itemShippingTaxAmount);
            $extensionAttributes->setMiraklBaseShippingTaxAmount($itemBaseShippingTaxAmount);
        }

        if ($miraklShippingExclTax) {
            /** @var \Magento\Sales\Api\Data\InvoiceExtension $extensionAttributes */
            $extensionAttributes = $invoice->getExtensionAttributes();
            $extensionAttributes->setMiraklShippingExclTax($miraklShippingExclTax);
            $extensionAttributes->setMiraklBaseShippingExclTax($miraklBaseShippingExclTax);
            $extensionAttributes->setMiraklShippingInclTax($miraklShippingInclTax);
            $extensionAttributes->setMiraklBaseShippingInclTax($miraklBaseShippingInclTax);
            $extensionAttributes->setMiraklShippingTaxAmount($miraklShippingTaxAmount);
            $extensionAttributes->setMiraklBaseShippingTaxAmount($miraklBaseShippingTaxAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $miraklShippingExclTax);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $miraklBaseShippingExclTax);
        }

        return $this;
    }
}
