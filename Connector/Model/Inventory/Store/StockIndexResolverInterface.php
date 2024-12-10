<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Inventory\Store;

interface StockIndexResolverInterface
{
    /**
     * @param int $storeId
     * @return StockIndexInterface
     */
    public function resolve(int $storeId): StockIndexInterface;
}
