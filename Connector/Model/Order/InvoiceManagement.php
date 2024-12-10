<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Order;

use Magento\Sales\Model\Order\Invoice;

class InvoiceManagement
{
    /**
     * @param Invoice $invoice
     * @param int     $miraklOfferId
     * @return bool
     */
    public function canIncludeMiraklOfferShipping(Invoice $invoice, $miraklOfferId)
    {
        /** @var Invoice $previousInvoice */
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->isCanceled() || !$previousInvoice->getId()) {
                continue;
            }
            /** @var Invoice\Item $previousInvoiceItem */
            foreach ($previousInvoice->getAllItems() as $previousInvoiceItem) {
                $orderItem = $previousInvoiceItem->getOrderItem();
                if ($orderItem->getMiraklOfferId() != $miraklOfferId) {
                    continue;
                }
                if ($orderItem->getMiraklShippingExclTax()) {
                    return false;
                }
            }
        }

        return true;
    }
}
