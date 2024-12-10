<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Product\Offer\Collector\Customizer;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\Product\Offer\Collector\Customizer\CustomizerInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\OfferIndexer\Model\Offer\Collection\AddStoreFilterToCollection;

class Store implements CustomizerInterface
{
    /**
     * @var AddStoreFilterToCollection
     */
    private $addStoreFilterToCollection;

    /**
     * @param AddStoreFilterToCollection $addStoreFilterToCollection
     */
    public function __construct(AddStoreFilterToCollection $addStoreFilterToCollection)
    {
        $this->addStoreFilterToCollection = $addStoreFilterToCollection;
    }

    /**
     * @inheritdoc
     */
    public function customize(Product $product, OfferCollection $collection): void
    {
        $this->addStoreFilterToCollection->execute($collection, $product->getStoreId());
    }
}
