<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\DB\Trigger\Creator;

use Mirakl\OfferIndexer\DB\Trigger\Creator\StockStatusTriggerCreator;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group db
 * @group trigger
 * @coversDefaultClass \Mirakl\OfferIndexer\DB\Trigger\Creator\StockStatusTriggerCreator
 * @covers \Mirakl\OfferIndexer\DB\Trigger\Creator\AbstractTriggerCreator
 */
class StockStatusTriggerCreatorTest extends TestCase
{
    /**
     * @var StockStatusTriggerCreator
     */
    private $stockStatusTriggerCreator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stockStatusTriggerCreator = $this->objectManager->create(StockStatusTriggerCreator::class);
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $stockId = 1;

        $expectedSql = <<<SQL
IF NEW.stock_status = 0 THEN
    SET @count_offers = (
        SELECT COUNT(offer_id)
        FROM `{$this->resource->getTableName('mirakl_offer_product_stock_index')}`
        WHERE product_id = NEW.product_id AND stock_id = $stockId
    );
    IF @count_offers > 0 THEN
        SET NEW.stock_status = 1;
    END IF;
END IF;
SQL;

        $actualSql = $this->stockStatusTriggerCreator->create($stockId);

        $this->assertEquals($expectedSql, $actualSql);
    }
}
