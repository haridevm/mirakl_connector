<?php

declare(strict_types=1);

namespace Mirakl\Connector\Block\Sales\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Tax\Model\Config as TaxConfig;
use Mirakl\Connector\Helper\Order as OrderHelper;

class Totals extends Template
{
    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @param Template\Context $context
     * @param TaxConfig        $taxConfig
     * @param OrderHelper      $orderHelper
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        TaxConfig $taxConfig,
        OrderHelper $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->taxConfig = $taxConfig;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $parent */
        $parent = $this->getParentBlock();

        $order = $parent->getOrder();

        if (!$this->orderHelper->isMiraklOrder($order)) {
            return $this;
        }

        $store = $order->getStore();

        if ($this->taxConfig->displaySalesShippingBoth($store)) {
            $totalExcl = new DataObject([
                'code'       => 'mirakl_shipping',
                'label'      => __('Marketplace Shipping (Excl. Tax)'),
                'value'      => $order->getMiraklShippingExclTax(),
                'base_value' => $order->getMiraklBaseShippingExclTax(),
            ]);

            $totalIncl = new DataObject([
                'code'       => 'mirakl_shipping_incl',
                'label'      => __('Marketplace Shipping (Incl. Tax)'),
                'value'      => $order->getMiraklShippingInclTax(),
                'base_value' => $order->getMiraklBaseShippingInclTax(),
            ]);

            $parent->addTotal($totalExcl, 'shipping_incl');
            $parent->addTotal($totalIncl, 'mirakl_shipping');
        } else {
            $displayIncl = $this->taxConfig->displaySalesShippingInclTax($store);

            $value = $displayIncl
                ? $order->getMiraklShippingInclTax()
                : $order->getMiraklShippingExclTax();

            $baseValue = $displayIncl
                ? $order->getMiraklBaseShippingInclTax()
                : $order->getMiraklBaseShippingExclTax();

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
