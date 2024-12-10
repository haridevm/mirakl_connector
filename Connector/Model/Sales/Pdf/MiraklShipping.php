<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory as OrderTaxCollectionFactory;

class MiraklShipping extends DefaultTotal
{
    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @param TaxHelper                 $taxHelper
     * @param TaxCalculation            $taxCalculation
     * @param OrderTaxCollectionFactory $ordersFactory
     * @param TaxConfig                 $taxConfig
     * @param array                     $data
     */
    public function __construct(
        TaxHelper $taxHelper,
        TaxCalculation $taxCalculation,
        OrderTaxCollectionFactory $ordersFactory,
        TaxConfig $taxConfig,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->taxConfig = $taxConfig;
    }

    /**
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $order = $this->getOrder();

        $amount = $order->formatPriceTxt($this->getAmount());
        $amountInclTax = $order->formatPriceTxt($this->getSource()->getMiraklShippingInclTax());

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $store = $order->getStore();

        if ($this->taxConfig->displaySalesShippingBoth($store)) {
            $totals = [
                [
                    'amount'    => $this->getAmountPrefix() . $amount,
                    'label'     => __('Marketplace Shipping (Excl. Tax)') . ':',
                    'font_size' => $fontSize,
                ],
                [
                    'amount'    => $this->getAmountPrefix() . $amountInclTax,
                    'label'     => __('Marketplace Shipping (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                ],
            ];
        } elseif ($this->taxConfig->displaySalesShippingInclTax($store)) {
            $totals = [
                [
                    'amount'    => $this->getAmountPrefix() . $amountInclTax,
                    'label'     => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        } else {
            $totals = [
                [
                    'amount'    => $this->getAmountPrefix() . $amount,
                    'label'     => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        }

        return $totals;
    }
}
