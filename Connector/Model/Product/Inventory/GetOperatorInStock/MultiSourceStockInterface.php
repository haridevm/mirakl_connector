<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock;

interface MultiSourceStockInterface
{
    /**
     * @param array $productIds
     * @param int   $stockId
     * @return array
     */
    public function getInStock(array $productIds, int $stockId): array;
}
