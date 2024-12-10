<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Model\Offer\RenderProduct;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Mirakl\Connector\Model\Offer;

class Creator implements CreatorInterface
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param ProductFactory $productFactory
     */
    public function __construct(ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(Offer $offer, Product $product): Product
    {
        $renderProduct = $this->productFactory->create();

        $renderProduct->setId($product->getId());
        $renderProduct->setSku($offer->getProductSku());
        $renderProduct->setPrice($offer->getPrice());
        $renderProduct->setQty($offer->getQuantity());
        $renderProduct->setTaxClassId($product->getTaxClassId());

        if ($offer->getOriginPrice() > $offer->getPrice()) {
            $renderProduct->setSpecialPrice($offer->getPrice());
            $renderProduct->setPrice($offer->getOriginPrice());
        }

        $renderProduct->setData('main_offer', $offer);

        return $renderProduct;
    }
}
