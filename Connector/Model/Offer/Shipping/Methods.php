<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Shipping;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\DataObject;
use Mirakl\Connector\Model\Offer\Shipping\Address as ShippingAddress;
use Mirakl\Connector\Model\Product\Offer\RealtimeProductOffers;
use Mirakl\MMP\Common\Domain\Offer\Shipping\ShippingPriceByZoneAndType;
use Mirakl\MMP\FrontOperator\Domain\Product\Offer\OfferOnProduct;

class Methods
{
    /**
     * @var RealtimeProductOffers
     */
    private $realtimeProductOffers;

    /**
     * @var ShippingAddress
     */
    private $shippingAddress;

    /**
     * @param RealtimeProductOffers $realtimeProductOffers
     * @param ShippingAddress       $shippingAddress
     */
    public function __construct(
        RealtimeProductOffers $realtimeProductOffers,
        ShippingAddress $shippingAddress
    ) {
        $this->realtimeProductOffers = $realtimeProductOffers;
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * Fetch available Mirakl shipping methods per offer for given skus and address using P11 API
     * Returns shipping types per sku and offer
     *
     * @param array            $skus
     * @param AddressInterface $shippingAddress
     * @return array
     */
    public function getShippingMethods(array $skus, $shippingAddress = null): array
    {
        if (is_numeric($shippingAddress)) {
            $shippingAddress = $this->shippingAddress->loadAddressById((int)$shippingAddress);
        }

        // Use default shipping address if no address is provided
        $shippingAddress = $shippingAddress ?: $this->shippingAddress->getCustomerDefaultShippingAddress();

        // Convert shipping address to quote address for shipping zone validation if needed
        if (!$shippingAddress instanceof DataObject) {
            $shippingAddress = $this->shippingAddress->convertToQuoteAddress($shippingAddress);
        }

        if (!$shippingZoneCode = $this->shippingAddress->getShippingZoneCode($shippingAddress)) {
            return [];
        }

        $skusRealtimeOffers = $this->realtimeProductOffers->get($skus, [$shippingZoneCode]);

        $shippingMethods = array_fill_keys($skus, []);
        foreach ($skusRealtimeOffers as $productSku => $skuRealtimeOffers) {
            foreach ($skuRealtimeOffers as $realtimeOffer) {
                /** @var OfferOnProduct $realtimeOffer */
                $shippingMethods[$productSku][$realtimeOffer->getId()] = $realtimeOffer->getShippingTypes()
                    ? $realtimeOffer->getShippingTypes()->getItems()
                    : [];
            }
        }

        return $shippingMethods;
    }

    /**
     * Return best offer shipping option based on price
     *
     * @param ShippingPriceByZoneAndType[] $shippingMethods
     * @return ShippingPriceByZoneAndType|null
     */
    public function getBestShippingMethodByPrice(array $shippingMethods): ?ShippingPriceByZoneAndType
    {
        if (!$shippingMethods) {
            return null;
        }

        $bestShippingMethod = array_shift($shippingMethods);
        foreach ($shippingMethods as $shippingMethod) {
            if ($shippingMethod->getShippingPriceUnit() < $bestShippingMethod->getShippingPriceUnit()) {
                $bestShippingMethod = $shippingMethod;
            } elseif ($shippingMethod->getShippingPriceUnit() === $bestShippingMethod->getShippingPriceUnit()) {
                // Keep method with the best delivery time if the price is the same
                $bestShippingMethod = $this->compareDeliveryTime($bestShippingMethod, $shippingMethod)
                                      ?: $bestShippingMethod;
            }
        }

        return $bestShippingMethod;
    }

    /**
     * Return best offer shipping option based on delivery date if provided
     *
     * @param ShippingPriceByZoneAndType[] $shippingMethods
     * @return ShippingPriceByZoneAndType|null
     */
    public function getBestShippingMethodByDate(array $shippingMethods): ?ShippingPriceByZoneAndType
    {
        $bestShippingMethod = null;
        foreach ($shippingMethods as $shippingMethod) {
            if ($shippingMethod->getDeliveryTime()) {
                $bestShippingMethod = $shippingMethod;
                break;
            }
        }

        if (empty($bestShippingMethod)) {
            return null;
        }

        foreach ($shippingMethods as $shippingMethod) {
            $bestShippingMethod = $this->compareDeliveryTime($bestShippingMethod, $shippingMethod)
                                  ?: $bestShippingMethod;
        }

        return $bestShippingMethod;
    }

    /**
     * Compares delivery time of 2 Mirakl shipping methods
     *
     * @param ShippingPriceByZoneAndType $shippingMethod1
     * @param ShippingPriceByZoneAndType $shippingMethod2
     * @return ShippingPriceByZoneAndType|null
     */
    private function compareDeliveryTime(
        ShippingPriceByZoneAndType $shippingMethod1,
        ShippingPriceByZoneAndType $shippingMethod2
    ): ?ShippingPriceByZoneAndType {
        if (!$shippingMethod1->getDeliveryTime() || !$shippingMethod2->getDeliveryTime()) {
            return null;
        }

        $earliestDays1 = $shippingMethod1->getDeliveryTime()->getEarliestDays();
        $latestDays1 = $shippingMethod1->getDeliveryTime()->getLatestDays();
        $price1 = $shippingMethod1->getShippingPriceUnit();
        $earliestDays2 = $shippingMethod2->getDeliveryTime()->getEarliestDays();
        $latestDays2 = $shippingMethod2->getDeliveryTime()->getLatestDays();
        $price2 = $shippingMethod2->getShippingPriceUnit();

        // In case 2 options have same shipping date, keep the one having the min price
        if (
            $earliestDays1 < $earliestDays2
            || ($earliestDays1 === $earliestDays2 && $latestDays1 < $latestDays2)
            || ($earliestDays1 === $earliestDays2 && $latestDays1 === $latestDays2 && $price1 < $price2)
        ) {
            return $shippingMethod1;
        }

        return $shippingMethod2;
    }
}
