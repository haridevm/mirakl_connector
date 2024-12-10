<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\BuyRequest\Validator;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Mirakl\Connector\Model\Offer;

class Association implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(DataObject $buyRequest, Product $product, Offer $offer): bool
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            /** @var Configurable $productType */
            $productType = $product->getTypeInstance();
            $child = $productType->getProductByAttributes($buyRequest['super_attribute'], $product);

            return $child && $child->getSku() === $offer->getProductSku();
        }

        return $offer->getProductSku() === $product->getSku();
    }
}
