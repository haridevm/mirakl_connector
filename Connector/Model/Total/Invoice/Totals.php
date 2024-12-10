<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Magento\Sales\Model\Order\Item as OrderItem;
use Mirakl\Connector\Model\Order\InvoiceManagement;

class Totals extends AbstractTotal
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
        if ($invoice->getOrder()->getMiraklShippingExclTax()) {
            // Adjust total amounts only if order has Mirakl shipping fees
            $this->adjustPartialInvoiceTotals($invoice);
            $this->adjustLastInvoiceTotals($invoice);
            $this->adjustOperatorShipping($invoice);
        }

        return $this;
    }

    /**
     * @param Invoice $invoice
     */
    protected function adjustOperatorShipping(Invoice $invoice)
    {
        if (!$invoice->getShippingAmount()) {
            return;
        }

        /** @var Invoice\Item $invoiceItem */
        foreach ($invoice->getItems() as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();
            if ($invoiceItem->getQty() > 0 && !$orderItem->isDummy() && !$orderItem->getMiraklOfferId()) {
                return;
            }
        }

        $orderShippingTaxAmount = $invoice->getOrder()->getShippingTaxAmount();
        $orderBaseShippingTaxAmount = $invoice->getOrder()->getBaseShippingTaxAmount();

        // We do not have operator items in invoice, so we have to reset the operator shipping amounts + taxes
        $invoice->setGrandTotal($invoice->getGrandTotal() - $invoice->getShippingAmount() - $orderShippingTaxAmount);
        $invoice->setBaseGrandTotal(
            $invoice->getBaseGrandTotal() - $invoice->getBaseShippingAmount() - $orderBaseShippingTaxAmount
        );
        $invoice->setTaxAmount($invoice->getTaxAmount() - $orderShippingTaxAmount);
        $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() - $orderBaseShippingTaxAmount);
        $invoice->setShippingTaxAmount(null);
        $invoice->setBaseShippingTaxAmount(null);
        $invoice->setShippingAmount(null);
        $invoice->setBaseShippingAmount(null);
        $invoice->setShippingInclTax(null);
        $invoice->setBaseShippingInclTax(null);
    }

    /**
     * @param Invoice $invoice
     */
    protected function adjustPartialInvoiceTotals(Invoice $invoice)
    {
        if ($invoice->isLast()) {
            return;
        }

        $miraklShippingTaxAmount     = 0;
        $miraklBaseShippingTaxAmount = 0;

        /** @var Invoice\Item $invoiceItem */
        foreach ($invoice->getItems() as $invoiceItem) {
            /** @var OrderItem $orderItem */
            $orderItem = $invoiceItem->getOrderItem();

            if (
                $invoiceItem->getQty() <= 0
                || !$this->invoiceManagement->canIncludeMiraklOfferShipping($invoice, $orderItem->getMiraklOfferId())
            ) {
                continue;
            }

            $miraklShippingTaxAmount     += $orderItem->getMiraklShippingTaxAmount()
                + $orderItem->getMiraklCustomShippingTaxAmount();
            $miraklBaseShippingTaxAmount += $orderItem->getMiraklBaseShippingTaxAmount()
                + $orderItem->getMiraklBaseCustomShippingTaxAmount();
        }

        if ($miraklShippingTaxAmount > 0) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $miraklShippingTaxAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $miraklBaseShippingTaxAmount);

            $invoice->setTaxAmount($invoice->getTaxAmount() + $miraklShippingTaxAmount);
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $miraklBaseShippingTaxAmount);
        }
    }

    /**
     * @param Invoice $invoice
     */
    protected function adjustLastInvoiceTotals(Invoice $invoice)
    {
        if (!$invoice->isLast()) {
            return;
        }

        /** @var OrderItem $orderItem */
        foreach ($invoice->getOrder()->getItems() as $orderItem) {
            if (!$orderItem->getLockedDoInvoice() || !$orderItem->getMiraklOfferId()) {
                continue;
            }

            // Magento does not take into account the locked_do_invoice field while building the subtotal
            // and the tax amount when the invoice is the last possible one. So we have to fix the
            // amounts by subtracting the Mirakl items row totals and shipping taxes.

            $invoice->setSubtotal($invoice->getSubtotal() - $orderItem->getRowTotal());
            $invoice->setBaseSubtotal($invoice->getBaseSubtotal() - $orderItem->getBaseRowTotal());

            $invoice->setGrandTotal($invoice->getGrandTotal()
                - $orderItem->getRowTotalInclTax()
                - $orderItem->getMiraklShippingTaxAmount()
                - $orderItem->getMiraklCustomShippingTaxAmount());

            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()
                - $orderItem->getBaseRowTotalInclTax()
                - $orderItem->getMiraklBaseShippingTaxAmount()
                - $orderItem->getMiraklBaseCustomShippingTaxAmount());

            $invoice->setTaxAmount($invoice->getTaxAmount()
                - $orderItem->getTaxAmount()
                - $orderItem->getMiraklShippingTaxAmount()
                - $orderItem->getMiraklCustomShippingTaxAmount());

            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount()
                - $orderItem->getBaseTaxAmount()
                - $orderItem->getMiraklBaseShippingTaxAmount()
                - $orderItem->getMiraklBaseCustomShippingTaxAmount());
        }
    }
}
