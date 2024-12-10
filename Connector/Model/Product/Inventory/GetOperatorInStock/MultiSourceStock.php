<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Inventory\GetOperatorInStock;

use Magento\Framework\App\ResourceConnection;

class MultiSourceStock implements MultiSourceStockInterface
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
    public function getInStock(array $productIds, int $stockId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->distinct()
            ->from(['issl' => $this->resourceConnection->getTableName('inventory_source_stock_link')], [])
            ->joinInner(
                ['is' => $this->resourceConnection->getTableName('inventory_source')],
                'issl.source_code = is.source_code',
                []
            )
            ->joinInner(
                ['isi' => $this->resourceConnection->getTableName('inventory_source_item')],
                'issl.source_code = isi.source_code',
                []
            )
            ->joinInner(
                ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'isi.sku = cpe.sku',
                ['product_id' => 'entity_id']
            )
            ->where('issl.stock_id = ?', $stockId)
            ->where('cpe.entity_id IN (?)', $productIds)
            ->where('is.enabled = ?', 1)
            ->where('isi.status = ?', 1);

        return $connection->fetchCol($select);
    }
}
