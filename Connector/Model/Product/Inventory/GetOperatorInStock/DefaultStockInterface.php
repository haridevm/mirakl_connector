<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock;

interface DefaultStockInterface
{
    /**
     * @param array $productIds
     * @return array
     */
    public function getInStock(array $productIds): array;
}