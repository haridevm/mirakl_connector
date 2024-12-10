<?php

namespace Mirakl\FrontendDemo\Block\Product\Offer;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Address;
use Mirakl\Connector\Model\Offer\Shipping\Price as ShippingPrice;

/**
 * @method Address getShippingAddress()
 * @method $this   setShippingAddress(Address $address)
 */
abstract class AbstractShippingMethod extends Template
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ShippingPrice
     */
    protected $shippingPrice;

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
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->shippingPrice = $shippingPrice;
    }

    /**
     * @return BlockInterface
     */
    public function getShippingDateBlock()
    {
        return $this->getLayout()->createBlock(ShippingDate::class);
    }

    /**
     * @param float $price
     * @param       $shippingAddress
     * @return float
     */
    public function getShippingPrice(float $price, $shippingAddress)
    {
        return $this->shippingPrice->getShippingPrice($price, $shippingAddress);
    }

    /**
     * @param float $price
     * @return string
     */
    public function convertAndFormat(float $price): string
    {
        return $this->priceCurrency->convertAndFormat($price);
    }
}
