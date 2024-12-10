<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Inventory\Store;

use Magento\CatalogInventory\Model\Stock;

class StockIndex implements StockIndexInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var string
     */
    private $table;

    /**
     * @param int $stockId
     * @param string $table
     */
    public function __construct(int $stockId, string $table)
    {
        $this->stockId = $stockId;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId(): int
    {
        return $this->stockId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * {@inheritdoc}
     */
    public function isDefaultStock(): bool
    {
        return $this->stockId === Stock::DEFAULT_STOCK_ID;
    }
}
