<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Process\VariantGroupCodeCleaner;
use Mirakl\Process\Model\Process;

class SaveAfterObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->isEnabled()) {
            return;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getData('product');

        // Update product(s) on Mirakl platform
        if ($product->getTypeId() === Product\Type::TYPE_SIMPLE) {
            $this->handleSimpleProduct($product);
        } elseif ($product->getTypeId() === Configurable::TYPE_CODE) {
            $this->handleConfigurableProduct($product);
        }
    }

    /**
     * @param Product $product
     * @return void
     */
    private function handleSimpleProduct(Product $product)
    {
        if ($product->getData('mirakl_sync') && $product->getData(McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER)) {
            $this->processHelper->exportProduct($product->getId());
        }
    }

    /**
     * @param Product $product
     * @return void
     */
    private function handleConfigurableProduct(Product $product)
    {
        if (!$vgc = $product->getData(McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE)) {
            return;
        }

        /** @var Configurable $productType */
        $productType = $product->getTypeInstance();
        $childrenIds = $productType->getUsedProductCollection($product)->getColumnValues('entity_id');

        $variants = $this->mcmHelper->findProductsByVariantId($vgc, Product\Type::TYPE_SIMPLE);
        $variantIds = $variants->getAllIds();

        /**
         * $childrenIds contains the real simple product ids associated to the current configurable product in Magento.
         * $variantIds contains the product ids with the same variant group code as the current configurable product.
         * The goal is to compare both product ids arrays to retrieve the variant product ids that are not linked to
         * the configurable product anymore in Magento (parent-child relationship removed in Magento).
         */
        $diffVariantIds = array_diff($variantIds, $childrenIds);

        if (empty($diffVariantIds)) {
            return;
        }

        /**
         * If some variant product ids have the same variant group code as the configurable product but are not linked
         * to it anymore, reset the variant group code of the variant products (children) in an asycnhronous process.
         */
        $process = $this->processFactory->create()
            ->setStatus(Process::STATUS_PENDING)
            ->setType(Process::TYPE_ADMIN)
            ->setName('MCM variant group code cleaner')
            ->setHelper(VariantGroupCodeCleaner::class)
            ->setMethod('execute')
            ->setParams([$vgc, $diffVariantIds]);

        $this->processResourceFactory->create()->save($process);
    }
}
