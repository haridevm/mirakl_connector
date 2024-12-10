<?php

namespace Mirakl\FrontendDemo\Block\Product\Offer;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\Connector\Model\Offer\Shipping\Price as ShippingPrice;
use Mirakl\MMP\Common\Domain\Offer\Shipping\ShippingPriceByZoneAndType;

class BestShippingMethod extends AbstractShippingMethod
{
    /**
     * @var ShippingPriceByZoneAndType
     */
    private $bestShippingMethodByPrice;

    /**
     * @var ShippingPriceByZoneAndType
     */
    private $bestShippingMethodByDate;

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'product/offer/best_shipping_method.phtml';

    /**
     * @param Context                $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param ShippingPrice          $shippingPrice
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        ShippingPrice $shippingPrice,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $priceCurrency,
            $shippingPrice,
            $data
        );
    }

    /**
     * @return ShippingPriceByZoneAndType|null
     */
    public function getBestShippingMethodByPrice(): ?ShippingPriceByZoneAndType
    {
        return $this->bestShippingMethodByPrice;
    }

    /**
     * @param ShippingPriceByZoneAndType|null $bestShippingMethodByPrice
     * @return $this
     */
    public function setBestShippingMethodByPrice(?ShippingPriceByZoneAndType $bestShippingMethodByPrice): self
    {
        $this->bestShippingMethodByPrice = $bestShippingMethodByPrice;

        return $this;
    }

    /**
     * @return ShippingPriceByZoneAndType|null
     */
    public function getBestShippingMethodByDate(): ?ShippingPriceByZoneAndType
    {
        return $this->bestShippingMethodByDate;
    }

    /**
     * @param ShippingPriceByZoneAndType|null $bestShippingMethodByDate
     * @return $this
     */
    public function setBestShippingMethodByDate(?ShippingPriceByZoneAndType $bestShippingMethodByDate): self
    {
        $this->bestShippingMethodByDate = $bestShippingMethodByDate;

        return $this;
    }
}
