<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock;

use Magento\Framework\App\ResourceConnection;

class DefaultStock implements DefaultStockInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function getInStock(array $productIds): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
                ['product_id']
            )
            ->where('stock_item.product_id IN (?)', $productIds)
            ->where('stock_item.is_in_stock = ?', 1);

        return $connection->fetchCol($select);
    }
}
