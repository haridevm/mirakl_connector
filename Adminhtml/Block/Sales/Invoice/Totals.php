<?php

declare(strict_types=1);

namespace Mirakl\Adminhtml\Block\Sales\Invoice;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Invoice;
use Magento\Tax\Model\Config as TaxConfig;
use Mirakl\Connector\Model\Order\InvoiceManagement;

class Totals extends Template
{
    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @var InvoiceManagement
     */
    protected $invoiceManagement;

    /**
     * @param Template\Context  $context
     * @param TaxConfig         $taxConfig
     * @param InvoiceManagement $invoiceManagement
     * @param array             $data
     */
    public function __construct(
        Template\Context $context,
        TaxConfig $taxConfig,
        InvoiceManagement $invoiceManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxConfig = $taxConfig;
        $this->invoiceManagement = $invoiceManagement;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();

        /** @var Invoice $invoice */
        $invoice = $parent->getSource();

        $miraklShippingExclTax     = $this->getMiraklShippingExclTax($invoice);
        $miraklBaseShippingExclTax = $this->getMiraklBaseShippingExclTax($invoice);
        $miraklShippingInclTax     = $this->getMiraklShippingInclTax($invoice);
        $miraklBaseShippingInclTax = $this->getMiraklBaseShippingInclTax($invoice);

        if (!$miraklShippingExclTax) {
            return $this;
        }

        if ($this->taxConfig->displaySalesShippingBoth($invoice->getStore())) {
            $totalExcl = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping (Excl. Tax)'),
                'value'      => $miraklShippingExclTax,
                'base_value' => $miraklBaseShippingExclTax,
            ]);

            $totalIncl = new DataObject([
                'code'       => 'mirakl_shipping_incl',
                'label'      => __('Marketplace Shipping (Incl. Tax)'),
                'value'      => $miraklShippingInclTax,
                'base_value' => $miraklBaseShippingInclTax,
            ]);

            $parent->addTotalBefore($totalExcl, 'tax');
            $parent->addTotalBefore($totalIncl, 'tax');
        } else {
            $displayIncl = $this->taxConfig->displaySalesShippingInclTax($invoice->getStore());

            $total = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping'),
                'value'      => $displayIncl ? $miraklShippingInclTax : $miraklShippingExclTax,
                'base_value' => $displayIncl ? $miraklBaseShippingInclTax : $miraklBaseShippingExclTax,
            ]);

            $parent->addTotalBefore($total, 'tax');
        }

        return $this;
    }

    /**
     * @param Invoice $invoice
     * @return float
     */
    protected function getMiraklBaseShippingExclTax(Invoice $invoice)
    {
        return $this->getAmount($invoice, 'mirakl_base_shipping_excl_tax');
    }

    /**
     * @param Invoice $invoice
     * @return float
     */
    protected function getMiraklBaseShippingInclTax(Invoice $invoice)
    {
        return $this->getAmount($invoice, 'mirakl_base_shipping_incl_tax');
    }

    /**
     * @param Invoice $invoice
     * @return float
     */
    protected function getMiraklShippingExclTax(Invoice $invoice)
    {
        return $this->getAmount($invoice, 'mirakl_shipping_excl_tax');
    }

    /**
     * @param Invoice $invoice
     * @return float
     */
    protected function getMiraklShippingInclTax(Invoice $invoice)
    {
        return $this->getAmount($invoice, 'mirakl_shipping_incl_tax');
    }

    /**
     * @param Invoice $invoice
     * @param string  $field
     * @return float
     */
    protected function getAmount(Invoice $invoice, $field)
    {
        if ($invoice->getData($field)) {
            return $invoice->getData($field);
        }

        $amount = 0;

        /** @var Invoice\Item $item */
        foreach ($invoice->getItems() as $item) {
            $orderItem = $item->getOrderItem();

            if (
                $item->getQty() <= 0 ||
                $orderItem->isDummy() ||
                !$orderItem->getMiraklOfferId() ||
                !$this->invoiceManagement->canIncludeMiraklOfferShipping($invoice, $orderItem->getMiraklOfferId())
            ) {
                continue;
            }

            if ($orderItem->getData($field)) {
                $amount += $orderItem->getData($field);
            }
        }

        return $amount;
    }
}
