<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\Collector\Customizer;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;

class Skus implements CustomizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function customize(Product $product, OfferCollection $collection): void
    {
        $collection->addProductSkuFilter($this->collectSkus($product));
    }

    /**
     * @param Product $product
     * @return array
     */
    private function collectSkus(Product $product): array
    {
        $skus = [$product->getSku()];

        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return $skus;
        }

        /** @var Configurable $productType */
        $productType = $product->getTypeInstance();

        return $productType->getUsedProductCollection($product)->getColumnValues('sku');
    }
}
