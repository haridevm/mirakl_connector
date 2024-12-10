<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Invoice;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Invoice;

class Totals extends \Mirakl\Connector\Block\Sales\Order\Totals
{
    /**
     * @inheritdoc
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();

        /** @var Invoice $invoice */
        $invoice = $parent->getSource();

        if (!$this->orderHelper->isMiraklOrder($parent->getOrder())) {
            return $this;
        }

        $store = $invoice->getStore();

        if ($this->taxConfig->displaySalesShippingBoth($store)) {
            $totalExcl = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping (Excl. Tax)'),
                'value'      => $invoice->getMiraklShippingExclTax(),
                'base_value' => $invoice->getMiraklBaseShippingExclTax(),
            ]);

            $totalIncl = new DataObject([
                'code'       => 'mirakl_shipping_incl',
                'label'      => __('Marketplace Shipping (Incl. Tax)'),
                'value'      => $invoice->getMiraklShippingInclTax(),
                'base_value' => $invoice->getMiraklBaseShippingInclTax(),
            ]);

            $parent->addTotal($totalExcl, 'shipping_incl');
            $parent->addTotal($totalIncl, 'mirakl_shipping');
        } else {
            $displayIncl = $this->taxConfig->displaySalesShippingInclTax($store);

            $value = $displayIncl
                ? $invoice->getMiraklShippingInclTax()
                : $invoice->getMiraklShippingExclTax();

            $baseValue = $displayIncl
                ? $invoice->getMiraklBaseShippingInclTax()
                : $invoice->getMiraklBaseShippingExclTax();

            $total = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping'),
                'value'      => $value,
                'base_value' => $baseValue,
            ]);

            $parent->addTotal($total, 'shipping');
        }

        return $this;
    }
}
