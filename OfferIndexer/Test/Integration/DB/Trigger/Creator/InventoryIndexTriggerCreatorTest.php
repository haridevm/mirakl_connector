<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\DB\Trigger\Creator;

use Mirakl\OfferIndexer\DB\Trigger\Creator\InventoryIndexTriggerCreator;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group db
 * @group trigger
 * @coversDefaultClass \Mirakl\OfferIndexer\DB\Trigger\Creator\InventoryIndexTriggerCreator
 * @covers \Mirakl\OfferIndexer\DB\Trigger\Creator\AbstractTriggerCreator
 */
class InventoryIndexTriggerCreatorTest extends TestCase
{
    /**
     * @var InventoryIndexTriggerCreator
     */
    private $inventoryIndexTriggerCreator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryIndexTriggerCreator = $this->objectManager->create(InventoryIndexTriggerCreator::class);
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $stockId = 1;

        $expectedSql = <<<SQL
IF NEW.is_salable = 0 THEN
    SET @count_offers = (
        SELECT COUNT(offer_id)
        FROM `{$this->resource->getTableName('mirakl_offer_product_stock_index')}`
        WHERE sku = NEW.sku AND stock_id = $stockId
    );
    IF @count_offers > 0 THEN
        SET NEW.is_salable = 1;
    END IF;
END IF;
SQL;

        $actualSql = $this->inventoryIndexTriggerCreator->create($stockId);

        $this->assertEquals($expectedSql, $actualSql);
    }
}
