<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory;

use Magento\Catalog\Model\Product;

class IsOperatorProductAvailable
{
    /**
     * @var GetOperatorStockInterface
     */
    private $getOperatorStock;

    /**
     * @param GetOperatorStockInterface $getOperatorStock
     */
    public function __construct(GetOperatorStockInterface $getOperatorStock)
    {
        $this->getOperatorStock = $getOperatorStock;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function execute(Product $product): bool
    {
        return $this->getOperatorStock->isInStock((int) $product->getId(), (int) $product->getStoreId());
    }
}
