<?php

declare(strict_types=1);

namespace Mirakl\Connector\Helper;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirakl\Connector\Model\Offer as OfferModel;
use Mirakl\Connector\Model\Offer\FinalPriceInterface;
use Mirakl\Connector\Model\OfferFactory;
use Mirakl\Connector\Model\Product\Offer\CollectorInterface as OfferCollectorInterface;
use Mirakl\Connector\Model\ResourceModel\OfferFactory as OfferResourceFactory;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\Core\Model\ResourceModel\Offer\State\Collection as OfferStateCollection;
use Mirakl\Core\Model\ResourceModel\Offer\State\CollectionFactory as StateCollectionFactory;
use Mirakl\Core\Model\ResourceModel\ShopFactory as ShopResourceFactory;
use Mirakl\Core\Model\ShopFactory;
use Mirakl\Core\Model\Shop as ShopModel;

/**
 * @SuppressWarnings(PHPMD)
 */
class Offer extends AbstractHelper
{
    /**
     * @var StateCollectionFactory
     */
    protected $stateCollectionFactory;

    /**
     * @var OfferFactory
     */
    protected $offerFactory;

    /**
     * @var OfferResourceFactory
     */
    protected $offerResourceFactory;

    /**
     * @var OfferCollectionFactory
     */
    protected $offerCollectionFactory;

    /**
     * @var ShopFactory
     */
    protected $shopFactory;

    /**
     * @var ShopResourceFactory
     */
    protected $shopResourceFactory;

    /**
     * @var OfferStateCollection
    */
    protected $states;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var FinalPriceInterface
     */
    protected $offerFinalPrice;

    /**
     * @var OfferCollectorInterface
     */
    protected $productOfferCollector;

    /**
     * @param Context                 $context
     * @param OfferFactory            $offerFactory
     * @param OfferResourceFactory    $offerResourceFactory
     * @param OfferCollectionFactory  $offerCollectionFactory
     * @param ShopFactory             $shopFactory
     * @param ShopResourceFactory     $shopResourceFactory
     * @param StateCollectionFactory  $stateCollectionFactory
     * @param StockStateInterface     $stockState
     * @param FinalPriceInterface     $offerFinalPrice
     * @param OfferCollectorInterface $productOfferCollector
     */
    public function __construct(
        Context $context,
        OfferFactory $offerFactory,
        OfferResourceFactory $offerResourceFactory,
        OfferCollectionFactory $offerCollectionFactory,
        ShopFactory $shopFactory,
        ShopResourceFactory $shopResourceFactory,
        StateCollectionFactory $stateCollectionFactory,
        StockStateInterface $stockState,
        FinalPriceInterface $offerFinalPrice,
        OfferCollectorInterface $productOfferCollector
    ) {
        parent::__construct($context);
        $this->offerFactory           = $offerFactory;
        $this->offerResourceFactory   = $offerResourceFactory;
        $this->offerCollectionFactory = $offerCollectionFactory;
        $this->shopFactory            = $shopFactory;
        $this->shopResourceFactory    = $shopResourceFactory;
        $this->stateCollectionFactory = $stateCollectionFactory;
        $this->stockState             = $stockState;
        $this->offerFinalPrice        = $offerFinalPrice;
        $this->productOfferCollector  = $productOfferCollector;
    }

    /**
     * Return the number of available offers for given product
     *
     * @param Product $product
     * @return int
     */
    public function countAvailableOffersForProduct($product)
    {
        return count($this->getAvailableOffersForProduct($product));
    }

    /**
     * Returns offer state collection
     *
     * @return OfferStateCollection
     */
    public function getAllConditions()
    {
        if (null === $this->states) {
            $this->states = $this->stateCollectionFactory->create();
        }

        return $this->states;
    }

    /**
     * Get available offers for a product
     *
     * @param Product   $product
     * @param int|array $excludeOfferIds
     * @return OfferCollection
     */
    public function getAvailableOffersForProduct(Product $product, $excludeOfferIds = null)
    {
        static $productsOffers = [];
        $cacheId = md5(serialize([$product->getId() => $excludeOfferIds])); // phpcs:ignore

        if (!isset($productsOffers[$cacheId])) {
            $offers = $this->productOfferCollector->collect($product);

            if (!empty($excludeOfferIds)) {
                $offers->excludeOfferIdsFilter($excludeOfferIds);
            }

            $productsOffers[$cacheId] = $offers;
        }

        return $productsOffers[$cacheId];
    }

    /**
     * Get available offers for a product sku and a currency code
     *
     * @param string|array $sku
     * @param string       $currencyCode
     * @param int|array    $excludeOfferIds
     * @param int|null     $storeId
     * @return OfferCollection
     * @deprecated Not used anymore
     */
    public function getAvailableOffersForProductSku($sku, $currencyCode, $excludeOfferIds = null, $storeId = null)
    {
        /** @var OfferCollection $collection */
        $collection = $this->offerCollectionFactory->create();

        $collection->joinProductIds()
            ->addProductsEnabledFilter($storeId)
            ->addAvailableFilter()
            ->addProductSkuFilter($sku)
            ->addCurrencyCodeFilter($currencyCode);

        if (!empty($excludeOfferIds)) {
            $collection->excludeOfferIdsFilter($excludeOfferIds);
        }

        $collection->setOrder('state_code', 'ASC');

        return $collection;
    }

    /**
     * Returns offer state matching specified state id
     *
     * @param int $stateId
     * @return string
     */
    public function getConditionNameById($stateId)
    {
        /** @var \Mirakl\Core\Model\Offer\State $state */
        $state = $this->getAllConditions()->getItemById($stateId);

        return $state ? $state->getName() : $stateId;
    }

    /**
     * Retrieve offer based on given offer id
     *
     * @param string $offerId
     * @return OfferModel
     */
    public function getOfferById($offerId)
    {
        $offer = $this->offerFactory->create();
        $this->offerResourceFactory->create()->load($offer, $offerId);

        return $offer;
    }

    /**
     * Returns condition name of specified offer
     *
     * @param OfferModel $offer
     * @return string
     */
    public function getOfferCondition(OfferModel $offer)
    {
        return $offer ? $this->getConditionNameById($offer->getStateId()) : '';
    }

    /**
     * @param OfferModel $offer
     * @param int|null   $qty
     * @return float
     * @deprecated Use \Mirakl\Connector\Model\Offer\FinalPrice instead
     */
    public function getOfferFinalPrice(OfferModel $offer, $qty = null)
    {
        return $this->offerFinalPrice->get($offer, $qty ? (int) $qty : null);
    }

    /**
     * Returns shop of specified offer if available
     *
     * @param OfferModel $offer
     * @return ShopModel
     */
    public function getOfferShop(OfferModel $offer)
    {
        /** @var ShopModel $shop */
        $shop = $this->shopFactory->create();
        $this->shopResourceFactory->create()->load($shop, $offer->getShopId());

        return $shop;
    }

    /**
     * Returns true if product has available offers
     *
     * @param Product $product
     * @return bool
     */
    public function hasAvailableOffersForProduct($product)
    {
        return $this->countAvailableOffersForProduct($product) > 0;
    }

    /**
     * Get the StockState object
     *
     * @return StockStateInterface
     */
    public function getStockState()
    {
        return $this->stockState;
    }
}
