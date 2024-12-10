<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer;

use Mirakl\Api\Helper\Offer as ProductOffersApi;
use Mirakl\Core\Helper\Config as CoreConfig;
use Mirakl\MMP\FrontOperator\Domain\Product\Offer\ProductWithOffers;

class RealtimeProductOffers
{
    /**
     * @var ProductOffersApi
     */
    private $productOffersApi;

    /**
     * @var CoreConfig
     */
    private $coreConfig;

    /**
     * @param ProductOffersApi $productOffersApi
     * @param CoreConfig       $coreConfig
     */
    public function __construct(
        ProductOffersApi $productOffersApi,
        CoreConfig $coreConfig
    ) {
        $this->productOffersApi = $productOffersApi;
        $this->coreConfig = $coreConfig;
    }

    /**
     * Fetches offers with real time data for a product using P11 API
     *
     * @param array      $skus
     * @param array|null $shippingZoneCodes
     * @return array
     */
    public function get(array $skus, array $shippingZoneCodes = null): array
    {
        $locale = $this->coreConfig->getLocale();
        $productWithOffersCollection = $this->productOffersApi->getOffersOnProducts(
            $skus,
            false,
            $locale,
            $shippingZoneCodes
        );

        $realtimeOffers = array_fill_keys($skus, []);

        // Magento cache can be used here if needed
        /** @var ProductWithOffers $productWithOffers */
        foreach ($productWithOffersCollection as $productWithOffers) {
            $realtimeOffers[$productWithOffers->getProduct()->getSku()] = $productWithOffers->getOffers()->getItems();
        }

        return $realtimeOffers;
    }
}
