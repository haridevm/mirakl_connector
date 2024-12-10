<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Plugin\Indexer\Inventory;

use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryMultiDimensionalIndexerApi\Model\Alias;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameBuilder;
use Mirakl\OfferIndexer\Plugin\Indexer\Inventory\IndexStructurePlugin;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group plugin
 * @coversDefaultClass \Mirakl\OfferIndexer\Plugin\Indexer\Inventory\IndexStructurePlugin
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexStructurePluginTest extends TestCase
{
    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->indexNameBuilder = $this->objectManager->create(IndexNameBuilder::class);
        $this->indexStructure = $this->objectManager->create(IndexStructure::class);
    }

    /**
     * @covers ::afterCreate
     * @covers ::getTable
     */
    public function testAfterCreate()
    {
        $indexName = $this->indexNameBuilder
            ->setIndexId(InventoryIndexer::INDEXER_ID)
            ->addDimension('stock_', '999')
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();

        $tableName = $this->resource->getTableName(InventoryIndexer::INDEXER_ID . '_stock_999');
        $triggerName = "mirakl_{$tableName}_product_with_offers";

        $this->resource->getConnection()->dropTable($tableName);

        $dbTriggers = $this->getDbTriggers();

        $this->assertArrayNotHasKey($triggerName, $dbTriggers);
        $this->assertFalse($this->resource->getConnection()->isTableExists($tableName));

        // Create the index will trigger the plugin
        $this->indexStructure->create($indexName, 'default');

        $dbTriggers = $this->getDbTriggers();

        $this->assertArrayHasKey($triggerName, $dbTriggers);
    }

    /**
     * @covers ::afterCreate
     */
    public function testAfterCreateWithBadPattern()
    {
        $indexName = $this->indexNameBuilder
            ->setIndexId(InventoryIndexer::INDEXER_ID)
            ->addDimension('stock_', 'foobar')
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();

        $triggerFactoryMock = $this->createMock(TriggerFactory::class);
        $triggerFactoryMock->expects($this->never())
            ->method('create');

        $indexStructurePlugin = $this->objectManager->create(IndexStructurePlugin::class, [
            'triggerFactory' => $triggerFactoryMock,
        ]);

        $indexStructurePlugin->afterCreate($this->indexStructure, null, $indexName, 'default');
    }
}
