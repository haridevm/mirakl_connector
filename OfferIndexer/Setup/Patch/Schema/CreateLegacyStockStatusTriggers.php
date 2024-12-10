<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Setup\Patch\Schema;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Mirakl\OfferIndexer\DB\Trigger\Creator\StockStatusTriggerCreator;

class CreateLegacyStockStatusTriggers implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var TriggerFactory
     */
    private $triggerFactory;

    /**
     * @var StockStatusTriggerCreator
     */
    private $triggerCreator;

    /**
     * @param ModuleDataSetupInterface  $setup
     * @param TriggerFactory            $triggerFactory
     * @param StockStatusTriggerCreator $triggerCreator
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        TriggerFactory $triggerFactory,
        StockStatusTriggerCreator $triggerCreator
    ) {
        $this->setup = $setup;
        $this->triggerCreator = $triggerCreator;
        $this->triggerFactory = $triggerFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->setup->startSetup();

        $this->createTriggers($this->setup);

        $this->setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function createTriggers(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        // Remove old triggers (Mirakl_Connector module version < 1.5.0)
        $connection->dropTrigger('mirakl_stock_product_with_offers');
        $connection->dropTrigger('mirakl_multi_inventory_product_with_offers');

        // Remove old triggers (Mirakl_Connector module version < 1.6.0)
        $connection->dropTrigger('mirakl_stock_product_with_offers_0');
        $connection->dropTrigger('mirakl_stock_product_with_offers_1');

        // Remove old triggers (Mirakl_Connector module version < 1.7.0)
        $connection->dropTrigger('mirakl_stock_product_with_offers_before_insert_0');
        $connection->dropTrigger('mirakl_stock_product_with_offers_before_insert_1');
        $connection->dropTrigger('mirakl_stock_product_with_offers_after_insert_0');
        $connection->dropTrigger('mirakl_stock_product_with_offers_after_insert_1');
        $connection->dropTrigger('mirakl_stock_product_with_offers_before_update_0');
        $connection->dropTrigger('mirakl_stock_product_with_offers_before_update_1');
        $connection->dropTrigger('mirakl_stock_product_with_offers_after_update_0');
        $connection->dropTrigger('mirakl_stock_product_with_offers_after_update_1');

        $stockStatusTables = [
            'cataloginventory_stock_status',
            'cataloginventory_stock_status_replica',
        ];

        // Handle product stock status index for operator products
        // without stock and with active offers (force stock status to 1)
        foreach ($stockStatusTables as $key => $tableName) {
            $stockStatusTable = $setup->getTable($tableName);
            if (!$connection->isTableExists($stockStatusTable)) {
                continue; // @codeCoverageIgnore
            }

            // Remove this column that is not used anymore
            $connection->dropColumn($stockStatusTable, 'mirakl_operator_stock_status');

            foreach ([Trigger::EVENT_INSERT, Trigger::EVENT_UPDATE] as $event) {
                $triggerName = sprintf('mirakl_stock_before_%s_%s', strtolower($event), $key);

                $trigger = $this->triggerFactory->create()
                    ->setName($triggerName)
                    ->setTime(Trigger::TIME_BEFORE)
                    ->setEvent($event)
                    ->setTable($stockStatusTable);

                $statement = $this->triggerCreator->create(Stock::DEFAULT_STOCK_ID);
                $trigger->addStatement($statement);

                $connection->dropTrigger($triggerName);
                $connection->createTrigger($trigger);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
