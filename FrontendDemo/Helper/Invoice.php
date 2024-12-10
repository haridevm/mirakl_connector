<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Invoice extends AbstractHelper
{
    /**
     * Filters specified invoice totals excluding amounts of Mirakl order specific amounts
     *
     * @deprecated Invoices amounts do not have to be filtered anymore since they already include the right values
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filterInvoiceTotals(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        return $this;
    }
}
