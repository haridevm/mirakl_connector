<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Inventory\Store;

interface StockIndexInterface
{
    /**
     * @return int
     */
    public function getStockId(): int;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @return bool
     */
    public function isDefaultStock(): bool;
}
