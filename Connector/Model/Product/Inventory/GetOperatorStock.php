<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory;

use Mirakl\Connector\Model\Inventory\Store\StockIndexResolverInterface;
use Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock\DefaultStockInterface;
use Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock\MultiSourceStockInterface;

class GetOperatorStock implements GetOperatorStockInterface
{
    /**
     * @var StockIndexResolverInterface
     */
    private $stockIndexResolver;

    /**
     * @var DefaultStockInterface
     */
    private $defaultStock;

    /**
     * @var MultiSourceStockInterface
     */
    private $multiSourceStock;

    /**
     * @param StockIndexResolverInterface $stockIndexResolver
     * @param DefaultStockInterface       $defaultStock
     * @param MultiSourceStockInterface   $multiSourceStock
     */
    public function __construct(
        StockIndexResolverInterface $stockIndexResolver,
        DefaultStockInterface $defaultStock,
        MultiSourceStockInterface $multiSourceStock
    ) {
        $this->stockIndexResolver = $stockIndexResolver;
        $this->defaultStock = $defaultStock;
        $this->multiSourceStock = $multiSourceStock;
    }

    /**
     * @inheritdoc
     */
    public function get(array $productIds, int $storeId): array
    {
        $inStockProducts = $this->getInStock($productIds, $storeId);

        return array_fill_keys($inStockProducts, 1) + array_fill_keys($productIds, 0);
    }

    /**
     * @inheritdoc
     */
    public function getInStock(array $productIds, int $storeId): array
    {
        $stockIndex = $this->stockIndexResolver->resolve($storeId);

        if ($stockIndex->isDefaultStock()) {
            // Handle default stock (and if MSI is disabled)
            return $this->defaultStock->getInStock($productIds);
        }

        // Handle MSI stock
        return $this->multiSourceStock->getInStock($productIds, $stockIndex->getStockId());
    }

    /**
     * @inheritdoc
     */
    public function isInStock(int $productId, int $storeId): bool
    {
        $stockStatus = $this->get([$productId], $storeId);

        return (bool) $stockStatus[$productId];
    }
}