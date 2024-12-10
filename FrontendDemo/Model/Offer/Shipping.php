<?php
namespace Mirakl\FrontendDemo\Model\Offer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Mirakl\Connector\Helper\Config;
use Mirakl\Connector\Helper\Offer as OfferHelper;
use Mirakl\Connector\Helper\Tax as ConnectorTaxHelper;
use Mirakl\Connector\Model\Offer;
use Mirakl\Connector\Model\Quote\Synchronizer as MiraklQuoteSynchronizer;
use Mirakl\Core\Exception\ShippingZoneNotFound;
use Mirakl\Core\Helper\ShippingZone as ShippingZoneHelper;
use Mirakl\Core\Model\ResourceModel\Shipping\Type as ShippingTypeResource;
use Mirakl\Core\Model\Shipping\TypeFactory as ShippingTypeFactory;

class Shipping
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ShippingZoneHelper
     */
    private $shippingZoneHelper;

    /**
     * @var MiraklQuoteSynchronizer
     */
    private $quoteSynchronizer;

    /**
     * @var ShippingTypeFactory
     */
    private $shippingTypeFactory;

    /**
     * @var ShippingTypeResource
     */
    private $shippingTypeResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @var Json
     */
    private $json;

    /**
     * @var OfferHelper
     */
    private $offerHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var array
     */
    private $shippingTypeLabels = [];

    /**
     * @param CheckoutSession         $checkoutSession
     * @param ShippingZoneHelper      $shippingZoneHelper
     * @param MiraklQuoteSynchronizer $quoteSynchronizer
     * @param ShippingTypeFactory     $shippingTypeFactory
     * @param ShippingTypeResource    $shippingTypeResource
     * @param StoreManagerInterface   $storeManager
     * @param Config                  $config
     * @param ConnectorTaxHelper      $connectorTaxHelper
     * @param Json                    $json
     * @param TaxHelper               $taxHelper
     * @param OfferHelper             $offerHelper
     * @param PriceCurrencyInterface  $priceCurrency
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ShippingZoneHelper $shippingZoneHelper,
        MiraklQuoteSynchronizer $quoteSynchronizer,
        ShippingTypeFactory $shippingTypeFactory,
        ShippingTypeResource $shippingTypeResource,
        StoreManagerInterface $storeManager,
        Config $config,
        ConnectorTaxHelper $connectorTaxHelper,
        Json $json,
        TaxHelper $taxHelper,
        OfferHelper $offerHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shippingZoneHelper = $shippingZoneHelper;
        $this->quoteSynchronizer = $quoteSynchronizer;
        $this->shippingTypeFactory = $shippingTypeFactory;
        $this->shippingTypeResource = $shippingTypeResource;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->connectorTaxHelper = $connectorTaxHelper;
        $this->taxHelper = $taxHelper;
        $this->json = $json;
        $this->offerHelper = $offerHelper;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param array $offerIds
     * @return array|false
     */
    public function getCustomerShippingEstimation(array $offerIds)
    {
        if (!$this->isCustomerHasShippingZone()) {
            return false;
        }
        $shippingEstimationByOffer = [];
        foreach ($offerIds as $offerId) {
            $offer = $this->offerHelper->getOfferById($offerId);
            $offerShippingTypes = $this->getDeliveryEstimation($offer);
            $offerShipping = [];
            foreach ($offerShippingTypes as $code => $shippingType) {
                $shippingDaysMessage = $this->getShippingDaysMessage($shippingType);
                $shippingEstimation = $this->getShippingTypeLabelByCode($code);
                if ($shippingType['price'] == 0) {
                    $shippingEstimation .= __(' : <b>Free shipping</b>') . (!empty($shippingDaysMessage) ? ' - ' : '') . $shippingDaysMessage;
                } else {
                    $shippingEstimation .= __(' : <b> %1 </b>', $this->priceCurrency->convertAndFormat($shippingType['price']))
                        . (!empty($shippingDaysMessage) ? ' - ' : '') . $shippingDaysMessage;
                }
                $offerShipping[] = $shippingEstimation;
            }
            $shippingEstimationByOffer[$offerId]['offer_shipping'] = $offerShipping;
            $shippingEstimationByOffer[$offerId]['best_offer_shipping'] = $this->getBestOfferShipping($offer);
        }

        return $shippingEstimationByOffer;
    }

    /**
     * @param Offer $offer
     * @return \Magento\Framework\Phrase|false
     */
    private function getBestOfferShipping(Offer $offer)
    {
        $bestShippingType = $this->getBestOfferShippingType($offer);
        if (empty($bestShippingType)) {
            return false;
        }

        $shippingDaysMessage = $this->getShippingDaysMessage($bestShippingType);

        if ($bestShippingType['price'] == 0) {
            return empty($shippingDaysMessage) ? __('Free shipping')
                : __(
                    'Free shipping<br>Estimated delivery: %2',
                    $this->priceCurrency->convertAndFormat($bestShippingType['price']),
                    $shippingDaysMessage
                );
        }

        return empty($shippingDaysMessage) ? __(
            '+ %1 shipping',
            $this->priceCurrency->convertAndFormat($bestShippingType['price'])
        )
            : __(
                '+ %1 shipping<br>Estimated delivery: %2',
                $this->priceCurrency->convertAndFormat($bestShippingType['price']),
                $shippingDaysMessage
            );
    }

    /**
     * Extract offer shipping prices by shipping method and delivery days estimation
     *
     * @param Offer $offer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDeliveryEstimation($offer)
    {
        $customerShippingAddress = $this->getCustomerShippingAddress();
        $customerShippingZoneCode = $this->getShippingZoneCode($customerShippingAddress);
        if (!$customerShippingZoneCode) {
            return [];
        }
        $additionalInfo = $offer->getAdditionalInfo();
        $leadTimeToShip = (int) $offer->getLeadtimeToShip();

        if (!$leadTimeToShip) {
            $shopAdditionalInfo = $offer->getShop()->getAdditionalInfo();
            $shopLeadTimeToShip = $shopAdditionalInfo['shipping']['lead_time_to_ship'] ?? 0;
            $leadTimeToShip = (int) $shopLeadTimeToShip;
        }

        if (empty($additionalInfo)) {
            return [];
        }

        $offerShippingTypes = [];
        foreach ($additionalInfo as $key => $value) {
            preg_match('/^shipping_price\[zone=(.+),method=(.+)\]$/', $key, $matches);
            if (!empty($matches[1]) || !empty($matches[2])) {
                list (, $priceShippingZone, $priceShippingType) = $matches;
                if ($priceShippingZone === $customerShippingZoneCode && $value !== '') {
                    $offerShippingTypes[$priceShippingType]['price'] = $this->getShippingPrice($value, $customerShippingAddress);
                    continue;
                }
            }
            preg_match('/^delivery_time_earliest_days\[zone=(.+),method=(.+)\]$/', $key, $matches);
            if (!empty($matches[1]) || !empty($matches[2])) {
                list (, $minDelayShippingZone, $minDelayShippingType) = $matches;
                if ($minDelayShippingZone === $customerShippingZoneCode) {
                    $offerShippingTypes[$minDelayShippingType]['min_days'] = !empty($value) ? $value + $leadTimeToShip : false;
                    continue;
                }
            }
            preg_match('/^delivery_time_latest_days\[zone=(.+),method=(.+)\]$/', $key, $matches);
            if (!empty($matches[1]) || !empty($matches[2])) {
                list (, $maxDelayShippingZone, $maxDelayShippingType) = $matches;
                if ($maxDelayShippingZone === $customerShippingZoneCode) {
                    $offerShippingTypes[$maxDelayShippingType]['max_days'] = !empty($value) ? $value + $leadTimeToShip : false;
                }
            }
        }

        foreach ($offerShippingTypes as $shippingTypeCode => $offerShippingType) {
            if (!isset($offerShippingType['price'])) {
                unset($offerShippingTypes[$shippingTypeCode]);
            }
        }

        // Sort shipping methods based on shipping price
        $shippingPrices = array_column($offerShippingTypes, 'price');
        array_multisort($shippingPrices, SORT_ASC, $offerShippingTypes);

        return $offerShippingTypes;
    }

    /**
     * Check if customer has a shipping zone
     *
     * @return bool
     */
    private function isCustomerHasShippingZone()
    {
        $customerShippingAddress = $this->getCustomerShippingAddress();

        return $this->getShippingZoneCode($customerShippingAddress) !== false;
    }

    /**
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    private function getCustomerShippingAddress()
    {
        $quote = $this->checkoutSession->getQuote();

        return $this->quoteSynchronizer->getQuoteShippingAddress($quote);
    }

    /**
     * @param Address $customerShippingAddress
     * @return string|false
     */
    private function getShippingZoneCode($customerShippingAddress)
    {
        try {
            $shippingZoneCode = $this->shippingZoneHelper->getShippingZoneCode($customerShippingAddress);
        } catch (ShippingZoneNotFound $e) {
            return false;
        }

        return $shippingZoneCode;
    }

    /**
     * @param string $shippingTypeCode
     * @return string
     */
    private function getShippingTypeLabelByCode(string $shippingTypeCode)
    {
        if (!isset($this->shippingTypeLabels[$shippingTypeCode])) {
            $shippingType = $this->shippingTypeFactory->create();
            $this->shippingTypeResource->load($shippingType, $shippingTypeCode, 'code');
            if ($shippingType->getId()) {
                $labelByLocale = $shippingType->getLabel();
                $labelByLocale = $this->json->unserialize($labelByLocale);
                $currentStore = $this->storeManager->getStore();
                $locale = $this->config->getLocale($currentStore);
                $this->shippingTypeLabels[$shippingTypeCode] = $labelByLocale[$locale] ?? $shippingTypeCode;
            } else {
                $this->shippingTypeLabels[$shippingTypeCode] = $shippingTypeCode;
            }
        }

        return $this->shippingTypeLabels[$shippingTypeCode];
    }

    /**
     * @param array $shippingType
     * @return \Magento\Framework\Phrase|string
     */
    private function getShippingDaysMessage(array $shippingType)
    {
        $minDays = $shippingType['min_days'] ?? 0;
        $maxDays = $shippingType['max_days'] ?? 0;
        if (empty($minDays) && empty($maxDays)) {
            return '';
        }
        if ($minDays === $maxDays) {
            return $maxDays == 1 ? __('<b>1</b> day', $maxDays) : __('<b>%1</b> days', $maxDays);
        }

        if (empty($minDays) || empty($maxDays)) {
            $nbDays = !empty($minDays) ? $minDays : $maxDays;
            return $nbDays == 1 ? __('<b>1</b> day', $nbDays) : __('<b>%1</b> days', $nbDays);
        }

        return __('between <b>%1</b> and <b>%2</b> days', $minDays, $maxDays);
    }

    /**
     * @param float   $price
     * @param Address $shippingAddress
     * @return float
     */
    private function getShippingPrice($price, $shippingAddress)
    {
        if ($this->taxHelper->displayShippingPriceExcludingTax()) {
            return $this->getShippingPriceExclTax($price, $shippingAddress);
        }

        return $this->getShippingPriceInclTax($price, $shippingAddress);
    }

    /**
     * @param float   $shippingPrice
     * @param Address $shippingAddress
     * @return float
     */
    private function getShippingPriceExclTax($shippingPrice, $shippingAddress)
    {
        if (!$this->config->getShippingPricesIncludeTax()) {
            return $shippingPrice;
        }

        return $this->connectorTaxHelper->getShippingPriceExclTax($shippingPrice, $shippingAddress);
    }

    /**
     * @param $shippingPrice
     * @param $shippingAddress
     * @return float
     */
    private function getShippingPriceInclTax($shippingPrice, $shippingAddress)
    {
        if ($this->config->getShippingPricesIncludeTax()) {
            return $shippingPrice;
        }

        return $this->connectorTaxHelper->getShippingPriceInclTax($shippingPrice, $shippingAddress);
    }

    /**
     * Return best offer shipping method details based on price
     *
     * @param Offer $offer
     * @return array
     */
    private function getBestOfferShippingType($offer)
    {
        $deliveryEstimation = $this->getDeliveryEstimation($offer);
        if (!$deliveryEstimation) {
            return [];
        }

        $bestShippingType = reset($deliveryEstimation);
        $bestShippingTypePrice = $bestShippingType['price'];
        foreach ($deliveryEstimation as $shippingType) {
            if ($shippingType['price'] < $bestShippingTypePrice) {
                $bestShippingType = $shippingType;
            }
        }

        return $bestShippingType;
    }
}