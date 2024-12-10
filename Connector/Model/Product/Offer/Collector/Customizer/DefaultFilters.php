<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\Collector\Customizer;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;

class DefaultFilters implements CustomizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function customize(Product $product, OfferCollection $collection): void
    {
        $collection->addStoreFilter($product->getStore());
    }
}
