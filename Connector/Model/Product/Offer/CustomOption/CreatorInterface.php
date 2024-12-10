<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\CustomOption;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\Offer;

interface CreatorInterface
{
    /**
     * @param Product $product
     * @param int     $offerId
     * @return Offer
     */
    public function create(Product $product, int $offerId): Offer;
}
