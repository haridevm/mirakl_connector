<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory;

interface GetOperatorStockInterface
{
    /**
     * Returns given products stock status as associative array for the given store:
     *
     * [<product_id> => <stock_status>, ...]
     *
     * @param array $productIds
     * @param int   $storeId
     * @return array
     */
    public function get(array $productIds, int $storeId): array;

    /**
     * Returns product ids that are in stock for the given store
     *
     * @param array $productIds
     * @param int   $storeId
     * @return array
     */
    public function getInStock(array $productIds, int $storeId): array;

    /**
     * Returns true if product is in stock for the given store, false otherwise
     *
     * @param int $productId
     * @param int $storeId
     * @return bool
     */
    public function isInStock(int $productId, int $storeId): bool;
}
