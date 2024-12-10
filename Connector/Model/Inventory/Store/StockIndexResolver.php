<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Inventory\Store;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;

class StockIndexResolver implements StockIndexResolverInterface
{
    /**
     * Need to use object manager if MSI is disabled
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @var StockIndexInterfaceFactory
     */
    private $stockIndexFactory;

    /**
     * @param ObjectManagerInterface     $objectManager
     * @param ResourceConnection         $resourceConnection
     * @param StockIdResolver            $stockIdResolver
     * @param StockIndexInterfaceFactory $stockIndexFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        StockIdResolver $stockIdResolver,
        StockIndexInterfaceFactory $stockIndexFactory
    ) {
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        $this->stockIdResolver = $stockIdResolver;
        $this->stockIndexFactory = $stockIndexFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(int $storeId): StockIndexInterface
    {
        $stockId = $this->stockIdResolver->resolve($storeId);

        if (Stock::DEFAULT_STOCK_ID === $stockId) {
            $stockIndexTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');
        } else {
            $stockIndexTable = $this->objectManager
                ->get('\Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface')
                ->execute($stockId);
        }

        return $this->stockIndexFactory->create([
            'stockId' => $stockId,
            'table'   => $stockIndexTable,
        ]);
    }
}
