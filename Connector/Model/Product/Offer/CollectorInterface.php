<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;

interface CollectorInterface
{
    /**
     * @param Product $product
     * @return OfferCollection
     */
    public function collect(Product $product): OfferCollection;
}
