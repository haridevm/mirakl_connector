<?php

declare(strict_types=1);

namespace Mirakl\Connector\Test;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Mirakl\MMP\Common\Domain\Offer\Shipping\DeliveryTime;
use Mirakl\MMP\Common\Domain\Offer\Shipping\ShippingPriceByZoneAndType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OfferShippingTestCase extends TestCase
{
    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $street
     * @param string|null $postcode
     * @param string|null $city
     * @param int|null    $regionId
     * @param string|null $countryId
     * @return QuoteAddress|MockObject
     */
    protected function createQuoteAddress(
        ?string $firstname,
        ?string $lastname,
        ?string $street,
        ?string $postcode,
        ?string $city,
        ?int $regionId,
        ?string $countryId
    ) {
        $quoteAddress = $this->getMockBuilder(QuoteAddress::class)
                             ->disableOriginalConstructor()->getMock();

        $quoteAddress->method('getFirstname')->willReturn($firstname);
        $quoteAddress->method('getLastname')->willReturn($lastname);
        $quoteAddress->method('getStreet')->willReturn($street);
        $quoteAddress->method('getPostcode')->willReturn($postcode);
        $quoteAddress->method('getCity')->willReturn($city);
        $quoteAddress->method('getRegionId')->willReturn($regionId);
        $quoteAddress->method('getCountryId')->willReturn($countryId);

        return $quoteAddress;
    }

    /**
     * @param string                  $code
     * @param string                  $label
     * @param float                   $price
     * @param int|null                $earliestDays
     * @param int|null                $latestDays
     * @param \DateTime|null          $earliestDate
     * @param \DateTime|null          $latestDate
     * @param string|null             $zoneCode
     * @param string|null             $zoneLabel
     * @param \DateTimeInterface|null $cutOffDate
     * @return ShippingPriceByZoneAndType
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    protected function createShippingMethod(
        string $code,
        string $label,
        float $price,
        ?int $earliestDays = null,
        ?int $latestDays = null,
        ?\DateTime $earliestDate = null,
        ?\DateTime $latestDate = null,
        ?string $zoneCode = null,
        ?string $zoneLabel = null,
        ?\DateTimeInterface $cutOffDate = null
    ) {
        $shippingMethod = new ShippingPriceByZoneAndType();
        $shippingMethod->setCode($code);
        $shippingMethod->setLabel($label);
        $shippingMethod->setShippingPriceUnit($price);
        if ($earliestDays && $latestDays) {
            $deliveryTime = new DeliveryTime();
            $deliveryTime->setEarliestDays($earliestDays);
            $deliveryTime->setEarliestDeliveryDate($earliestDate);
            $deliveryTime->setLatestDays($latestDays);
            $deliveryTime->setLatestDeliveryDate($latestDate);
            $shippingMethod->setDeliveryTime($deliveryTime);
        }

        if ($cutOffDate) {
            $shippingMethod->setCutOffTime($cutOffDate->format('H:i'));
            $shippingMethod->setCutOffNextDate($cutOffDate);
        }

        $shippingMethod->setShippingZoneCode($zoneCode);
        $shippingMethod->setShippingZoneLabel($zoneLabel);

        return $shippingMethod;
    }
}
