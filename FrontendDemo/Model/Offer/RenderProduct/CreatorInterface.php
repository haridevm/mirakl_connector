<?php
declare(strict_types=1);

namespace Mirakl\FrontendDemo\Model\Offer\RenderProduct;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\Offer;

interface CreatorInterface
{
    /**
     * @param Offer   $offer
     * @param Product $product
     * @return Product
     */
    public function create(Offer $offer, Product $product): Product;
}