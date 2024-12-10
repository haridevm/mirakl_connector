<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\CustomOption;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\Offer;
use Mirakl\Connector\Model\OfferFactory;
use Mirakl\Connector\Model\ResourceModel\OfferFactory as OfferResourceFactory;

class Creator implements CreatorInterface
{
    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * @var OfferResourceFactory
     */
    private $offerResourceFactory;

    /**
     * @param OfferFactory $offerFactory
     * @param OfferResourceFactory $offerResourceFactory
     */
    public function __construct(
        OfferFactory $offerFactory,
        OfferResourceFactory $offerResourceFactory
    ) {
        $this->offerFactory = $offerFactory;
        $this->offerResourceFactory = $offerResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Product $product, int $offerId): Offer
    {
        $offer = $this->offerFactory->create();
        $offer->setStoreId($product->getStoreId());
        $this->offerResourceFactory->create()->load($offer, $offerId);

        return $offer;
    }
}
