<?php

declare(strict_types=1);

namespace Mirakl\Connector\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Mirakl\Connector\Model\Inventory\Store\StockIdResolver;

class StockQty extends AbstractHelper
{
    /**
     * Need to use object manager if MSI is disabled
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Stock
     */
    protected $stockHelper;

    /**
     * @var StockIdResolver
     */
    protected $stockIdResolver;

    /**
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param Stock                  $stockHelper
     * @param StockIdResolver        $stockIdResolver
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Stock $stockHelper,
        StockIdResolver $stockIdResolver
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->stockHelper = $stockHelper;
        $this->stockIdResolver = $stockIdResolver;
    }

    /**
     * Returns stock quantity of specified product according to
     * current website and potential multi inventory configuration.
     *
     * @param Product $product
     * @return float
     */
    public function getProductStockQty(Product $product)
    {
        if (!$this->stockHelper->isMultiInventoryEnabled()) {
            return $this->stockHelper->getProductStockQty($product);
        }

        $products = [];
        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            /** @var Grouped $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $products = $typeInstance->getAssociatedProducts($product);
        } elseif ($product->getTypeId() == Configurable::TYPE_CODE) {
            /** @var Configurable $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $products = $typeInstance->getUsedProductCollection($product);
        } else {
            $products[] = $product;
        }

        $qty = 0;
        $stockId = $this->stockIdResolver->resolve($product->getStoreId());

        foreach ($products as $_product) {
            try {
                $qty += $this->objectManager->get('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface')
                    ->execute($_product->getSku(), $stockId);
            } catch (InputException $e) {
                // Ignore exception
            }
        }

        return $qty;
    }
}
