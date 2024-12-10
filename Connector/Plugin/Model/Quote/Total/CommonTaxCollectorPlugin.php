<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\Quote\Total;

use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Connector\Model\Tax\Calculator as TaxCalculator;

class CommonTaxCollectorPlugin
{
    /**
     * @var ConnectorConfig
     */
    private $connectorConfig;

    /**
     * @var TaxCalculator
     */
    private $taxCalculator;

    /**
     * @param ConnectorConfig $connectorConfig
     * @param TaxCalculator   $taxCalculator
     */
    public function __construct(
        ConnectorConfig $connectorConfig,
        TaxCalculator $taxCalculator
    ) {
        $this->connectorConfig = $connectorConfig;
        $this->taxCalculator   = $taxCalculator;
    }

    /**
     * @param CommonTaxCollector               $subject
     * @param \Closure                         $proceed
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Quote\Item\AbstractItem          $item
     * @param bool                             $priceIncludesTax
     * @param bool                             $useBaseCurrency
     * @param string                           $parentCode
     * @return mixed
     */
    public function aroundMapItem(
        CommonTaxCollector $subject,
        \Closure $proceed,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Quote\Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $product = $item->getProduct();
        if ($product && $product->getCustomOption('mirakl_offer')) {
            $priceIncludesTax = $this->connectorConfig->getOffersIncludeTax($item->getStore());
        }

        return $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
    }

    /**
     * @param CommonTaxCollector      $subject
     * @param \Closure                $proceed
     * @param Quote\Item\AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store                   $store
     * @return CommonTaxCollector
     */
    public function aroundUpdateItemTaxInfo(
        CommonTaxCollector $subject,
        \Closure $proceed,
        $quoteItem,
        $itemTaxDetails,
        $baseItemTaxDetails,
        $store
    ) {
        if ($miraklTaxesApplied = $quoteItem->getMiraklCustomTaxApplied()) {
            $miraklTaxes = unserialize($miraklTaxesApplied); // phpcs:ignore
            if (!empty($miraklTaxes['taxes'])) {
                $this->taxCalculator
                    ->addMiraklTaxesToTaxItems($itemTaxDetails, $baseItemTaxDetails, $miraklTaxes['taxes']);
            }
        }

        return $proceed($quoteItem, $itemTaxDetails, $baseItemTaxDetails, $store);
    }
}
