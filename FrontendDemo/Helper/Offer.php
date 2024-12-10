<?php
namespace Mirakl\FrontendDemo\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirakl\Connector\Helper\Offer as ConnectorOfferHelper;
use Mirakl\Connector\Helper\StockQty as StockQtyHelper;
use Mirakl\Connector\Model\Offer as OfferModel;
use Mirakl\Connector\Model\Product\Inventory\IsOperatorProductAvailable;
use Mirakl\Core\Model\Shop;

class Offer extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConnectorOfferHelper
     */
    protected $connectorOfferHelper;

    /**
     * @var StockQtyHelper
     */
    protected $stockQtyHelper;

    /**
     * @var IsOperatorProductAvailable
     */
    protected $isOperatorProductAvailable;

    /**
     * @param Context $context
     * @param Config $config
     * @param ConnectorOfferHelper $connectorOfferHelper
     * @param StockQtyHelper $stockQtyHelper
     * @param IsOperatorProductAvailable $isOperatorProductAvailable
     */
    public function __construct(
        Context $context,
        Config $config,
        ConnectorOfferHelper $connectorOfferHelper,
        StockQtyHelper $stockQtyHelper,
        IsOperatorProductAvailable $isOperatorProductAvailable
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->connectorOfferHelper = $connectorOfferHelper;
        $this->stockQtyHelper = $stockQtyHelper;
        $this->isOperatorProductAvailable = $isOperatorProductAvailable;
    }

    /**
     * Get the first item to display
     *
     * @param   Product $product
     * @return  OfferModel|null
     */
    public function getBestOffer(Product $product)
    {
        if ($this->isOperatorProductAvailable($product)) {
            return null;
        }

        $offers = $this->getAllOffers($product);

        return array_shift($offers);
    }

    /**
     * Get the product with the min price and stock qty > 0
     *
     * @param   Product $product
     * @return  Product|null
     */
    public function getBestOperatorOffer(Product $product)
    {
        if ($product->getTypeId() != Configurable::TYPE_CODE) {
            return $this->isOperatorProductAvailable($product) ? $product : null;
        }

        if ($product->hasData('best_operator_offer')) {
            return $product->getData('best_operator_offer');
        }

        $bestProduct = null;
        $minPrice = null;

        /** @var Product $item */
        foreach ($product->getTypeInstance()->getUsedProducts($product) as $item) {
            if ($this->isOperatorProductAvailable($item)) {
                $price = $item->getPriceInfo()->getPrice('final_price')->getAmount();

                if (!$minPrice || $minPrice > $price) {
                    $minPrice = $price;
                    $bestProduct = $item;
                }
            }
        }

        $product->setData('best_operator_offer', $bestProduct);

        return $bestProduct;
    }

    /**
     * @param   Product     $product
     * @return  bool
     */
    public function isOperatorProductAvailable(Product $product)
    {
        if (!$product->hasData('operator_product_available')) {
            $isAvailable = $product->isSalable() && $this->isOperatorProductAvailable->execute($product);
            $product->setData('operator_product_available', $isAvailable);
        }

        return $product->getData('operator_product_available');
    }

    /**
     * Indicates whether the specified offer is at New state or not
     *
     * @param   OfferModel  $offer
     * @return  bool
     */
    public function isProductNew(OfferModel $offer)
    {
        return $offer->getStateCode() === $this->config->getNewOfferStateId();
    }

    /**
     * Sort given offers array by new state and price
     *
     * @param   OfferModel[]    $offers
     * @return  void
     */
    public function sortOffers(array &$offers)
    {
        uasort($offers, function ($offer1, $offer2) {
            /** @var OfferModel $offer1 */
            /** @var OfferModel $offer2 */
            if ($offer1->getStateCode() == $offer2->getStateCode()) {
                $totalPriceOffer1 = $offer1->getPrice() + $offer1->getMinShippingPrice();
                $totalPriceOffer2 = $offer2->getPrice() + $offer2->getMinShippingPrice();

                return $totalPriceOffer1 < $totalPriceOffer2 ? -1 : 1;
            }

            if ($this->isProductNew($offer1)) {
                return -1;
            }

            return $offer1->getStateCode() > $offer2->getStateCode() ? -1 : 1;
        });
    }

    /**
     * Get all offers
     *
     * @param   Product     $product
     * @param   int|array   $excludeOfferIds
     * @return  OfferModel[]
     */
    public function getAllOffers(Product $product, $excludeOfferIds = null)
    {
        /** @var OfferModel[] $offers */
        $offers = $this->connectorOfferHelper
            ->getAvailableOffersForProduct($product, $excludeOfferIds)
            ->getItems();

        $this->sortOffers($offers);

        return $offers;
    }

    /**
     * Get store name
     *
     * @param   Product $product
     * @return  string
     */
    public function getStoreName(Product $product)
    {
        return $this->config->getStoreName($product->getStore());
    }

    /**
     * Return if product has available offer
     *
     * @param   Product $product
     * @return  bool
     */
    public function hasAvailableOffersForProduct($product)
    {
        return $this->connectorOfferHelper->hasAvailableOffersForProduct($product);
    }

    /**
     * Returns shop of specified offer if available
     *
     * @param   OfferModel  $offer
     * @return  Shop
     */
    public function getOfferShop(OfferModel $offer)
    {
        return $this->connectorOfferHelper->getOfferShop($offer);
    }

    /**
     * Returns condition name of specified offer
     *
     * @param   OfferModel  $offer
     * @return  string
     */
    public function getOfferCondition(OfferModel $offer)
    {
        return $this->connectorOfferHelper->getOfferCondition($offer);
    }

    /**
     * @param   Product $product
     * @return  array
     */
    public function getOffersSummary(Product $product)
    {
        $summary = [];
        $bestOffer = $this->getBestOffer($product);

        /** @var OfferModel $offer */
        foreach ($this->getAllOffers($product) as $offer) {
            if ($bestOffer && $bestOffer->getId() == $offer->getId()) {
                continue; // Best offer is already displayed, remove it from summary
            }
            $state = $offer->getConditionName();
            if (key_exists($state, $summary)) {
                $summary[$state]['nb'] += 1;
                if ($offer->getPrice() < $summary[$state]['best_price'] || $summary[$state]['best_price'] == 0) {
                    $summary[$state]['best_price'] = $offer->getPrice();
                }
            } else {
                $summary[$state] = ['nb' => 1, 'best_price' => $offer->getPrice()];
            }
        }

        return $summary;
    }

    /**
     * @param   OfferModel  $offer
     * @return  array
     */
    public function getOfferData(OfferModel $offer)
    {
        $shop = $offer->getShop();

        return [
            'priceAdditionalInfo'  => $offer->getPriceAdditionalInfo(),
            'conditionLabel'       => $offer->getConditionName(),
            'stock'                => $offer->getQuantity(),
            'type'                 => 'offer',
            'offerId'              => $offer->getId(),
            'productSku'           => $offer->getProductSku(),
            'soldBy'               => $offer->getShopName(),
            'soldByUrl'            => $shop->getUrl(),
            'shopEvaluationsCount' => $shop->getEvaluationsCount(),
            'shopEvaluation'       => $shop->getFormattedGrade(),
        ];
    }
}
