<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Plugin\Indexer\Inventory;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexName;
use Mirakl\OfferIndexer\DB\Trigger\Creator\InventoryIndexTriggerCreator;

class IndexStructurePlugin
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var TriggerFactory
     */
    private $triggerFactory;

    /**
     * Need to use object manager in order to keep compatibility
     * with version 2.2.x of Magento
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InventoryIndexTriggerCreator
     */
    private $triggerCreator;

    /**
     * @param ResourceConnection $resourceConnection
     * @param TriggerFactory $triggerFactory
     * @param ObjectManagerInterface $objectManager
     * @param InventoryIndexTriggerCreator $triggerCreator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TriggerFactory $triggerFactory,
        ObjectManagerInterface $objectManager,
        InventoryIndexTriggerCreator $triggerCreator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->triggerFactory = $triggerFactory;
        $this->objectManager = $objectManager;
        $this->triggerCreator = $triggerCreator;

        $this->indexNameResolver = $this->objectManager
            ->get('Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface');
    }

    /**
     * @param string $table
     * @return string
     */
    private function getTable(string $table): string
    {
        return $this->resourceConnection->getTableName($table);
    }

    /**
     * @param IndexStructure $subject
     * @param null           $result
     * @param IndexName      $indexName
     * @param string         $connectionName
     */
    public function afterCreate(IndexStructure $subject, $result, IndexName $indexName, $connectionName)
    {
        $pattern = InventoryIndexer::INDEXER_ID . '_stock_(\d+)';
        $tableName = $this->indexNameResolver->resolveName($indexName);

        if (1 !== preg_match("#$pattern#", $tableName, $matches)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection($connectionName);

        $trigger = $this->triggerFactory->create()
            ->setName('mirakl_' . $tableName . '_product_with_offers')
            ->setTime(Trigger::TIME_BEFORE)
            ->setEvent(Trigger::EVENT_INSERT)
            ->setTable($this->getTable($tableName));

        $stockId = (int) $matches[1];

        $statement = $this->triggerCreator->create($stockId);
        $trigger->addStatement($statement);
        $connection->dropTrigger($trigger->getName());
        $connection->createTrigger($trigger);
    }
}
