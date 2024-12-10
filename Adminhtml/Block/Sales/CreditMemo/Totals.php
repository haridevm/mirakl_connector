<?php
namespace Mirakl\Adminhtml\Block\Sales\CreditMemo;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Tax\Model\Config as TaxConfig;

class Totals extends Template
{
    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @param   Template\Context    $context
     * @param   TaxConfig           $taxConfig
     * @param   array               $data
     */
    public function __construct(
        Template\Context $context,
        TaxConfig $taxConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxConfig = $taxConfig;
    }

    /**
     * @return  $this
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();

        /** @var Creditmemo $creditMemo */
        $creditMemo = $parent->getSource();

        $miraklShippingExclTax     = $creditMemo->getMiraklShippingExclTax();
        $miraklBaseShippingExclTax = $creditMemo->getMiraklBaseShippingExclTax();
        $miraklShippingInclTax     = $creditMemo->getMiraklShippingInclTax();
        $miraklBaseShippingInclTax = $creditMemo->getMiraklBaseShippingInclTax();

        if ($this->taxConfig->displaySalesShippingBoth($creditMemo->getStore())) {
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

            $parent->addTotalBefore($totalExcl, 'adjustment_positive');
            $parent->addTotalBefore($totalIncl, 'adjustment_positive');
        } else {
            $displayIncl = $this->taxConfig->displaySalesShippingInclTax($creditMemo->getStore());

            $total = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping'),
                'value'      => $displayIncl ? $miraklShippingInclTax : $miraklShippingExclTax,
                'base_value' => $displayIncl ? $miraklBaseShippingInclTax : $miraklBaseShippingExclTax,
            ]);

            $parent->addTotalBefore($total, 'adjustment_positive');
        }

        return $this;
    }
}