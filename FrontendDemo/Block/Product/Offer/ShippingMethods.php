<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Product\Offer;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\Connector\Model\Offer\Shipping\Price as ShippingPrice;
use Mirakl\MMP\Common\Domain\Offer\Shipping\ShippingPriceByZoneAndType;

class ShippingMethods extends AbstractShippingMethod
{
    /**
     * @var ShippingPriceByZoneAndType[]
     */
    private $shippingMethods;

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'product/offer/shipping_methods.phtml';

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
     * @return ShippingPriceByZoneAndType[]|null
     */
    public function getShippingMethods(): ?array
    {
        return $this->shippingMethods;
    }

    /**
     * @param ShippingPriceByZoneAndType[]|null $shippingMethods
     * @return $this
     */
    public function setShippingMethods(?array $shippingMethods)
    {
        $this->shippingMethods = $shippingMethods;

        return $this;
    }
}
