<?php

namespace Mirakl\Connector\Model\Offer\Shipping;

use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Helper\Data as TaxHelper;
use Mirakl\Connector\Helper\Config;
use Mirakl\Connector\Helper\Tax as ConnectorTaxHelper;

class Price
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConnectorTaxHelper
     */
    private $connectorTaxHelper;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param Config             $config
     * @param ConnectorTaxHelper $connectorTaxHelper
     * @param TaxHelper          $taxHelper
     */
    public function __construct(
        Config $config,
        ConnectorTaxHelper $connectorTaxHelper,
        TaxHelper $taxHelper
    ) {
        $this->config = $config;
        $this->connectorTaxHelper = $connectorTaxHelper;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @param float        $price
     * @param Address|null $shippingAddress
     * @return float|int
     */
    public function getShippingPrice(float $price, ?Address $shippingAddress = null)
    {
        if ($this->taxHelper->displayShippingPriceExcludingTax()) {
            return $this->getShippingPriceExclTax($price, $shippingAddress);
        }

        return $this->getShippingPriceInclTax($price, $shippingAddress);
    }

    /**
     * @param float        $shippingPrice
     * @param Address|null $shippingAddress
     * @return float|int
     */
    private function getShippingPriceExclTax(float $shippingPrice, ?Address $shippingAddress = null)
    {
        if (!$this->config->getShippingPricesIncludeTax()) {
            return $shippingPrice;
        }

        return $this->connectorTaxHelper->getShippingPriceExclTax($shippingPrice, $shippingAddress);
    }

    /**
     * @param float        $shippingPrice
     * @param Address|null $shippingAddress
     * @return float|int
     */
    private function getShippingPriceInclTax(float $shippingPrice, ?Address $shippingAddress = null)
    {
        if ($this->config->getShippingPricesIncludeTax()) {
            return $shippingPrice;
        }

        return $this->connectorTaxHelper->getShippingPriceInclTax($shippingPrice, $shippingAddress);
    }
}
