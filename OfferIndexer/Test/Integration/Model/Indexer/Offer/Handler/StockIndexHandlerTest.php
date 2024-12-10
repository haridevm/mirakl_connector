<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer\Handler;

use Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\StockIndexHandler;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\StockIndexHandler
 * @covers ::__construct
 * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex::_construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class StockIndexHandlerTest extends TestCase
{
    /**
     * @var StockIndexHandler
     */
    private $offerStockIndexHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->offerStockIndexHandler = $this->objectManager->create(StockIndexHandler::class, [
            'offerAvailability' => $this->availabilityMock,
        ]);
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex::clear
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearPartial()
    {
        $this->offerStockIndexHandler->clear(['24-MB01']);

        $this->assertCount(7, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex::truncate
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearAll()
    {
        $this->offerStockIndexHandler->clear();

        $this->assertCount(0, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::clear
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearWithUnavailableSkus()
    {
        $this->offerStockIndexHandler->clear(['foo', 'bar']);

        $this->assertCount(11, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::update
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex::update
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testUpdate()
    {
        $this->offerStockIndexHandler->update([
            [
                'offer_id'   => 2258,
                'product_id' => 5,
                'sku'        => '24-MB06-foo',
                'stock_id'   => 1,
            ],
        ]);

        $rows = $this->fetchAll($this->offerStockIndexResource->getMainTable(), [
            'offer_id = ?'   => 2258,
            'product_id = ?' => 5,
            'stock_id = ?'   => 1,
        ]);

        $this->assertEquals('24-MB06-foo', $rows[0]['sku']);
    }

    /**
     * @covers ::build
     * @covers ::getStockId
     * @covers ::processParentProducts
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testBuild()
    {
        $this->availabilityMock->expects($this->exactly(4))
            ->method('validate')
            ->willReturn(true);

        $store = $this->storeManager->getStore();

        $collection = $this->offerCollectionFactory->create();
        $collection->addStoreFilter($store);
        $collection->addFieldToFilter('offer_id', ['in' => ['2231', '2259', '2260', '2261']]);

        $result = $this->offerStockIndexHandler->build($collection, $store);

        $this->assertEquals([
            [
                'offer_id'   => 2231,
                'product_id' => 1,
                'sku'        => '24-MB01',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2259,
                'product_id' => 488,
                'sku'        => 'MS11-L-Blue',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2260,
                'product_id' => 493,
                'sku'        => 'MS11-XL-Yellow',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2261,
                'product_id' => 486,
                'sku'        => 'MS11-M-Green',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2259,
                'product_id' => 494,
                'sku'        => 'MS11',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2260,
                'product_id' => 494,
                'sku'        => 'MS11',
                'stock_id'   => 1,
            ],
            [
                'offer_id'   => 2261,
                'product_id' => 494,
                'sku'        => 'MS11',
                'stock_id'   => 1,
            ],
        ], $result);
    }

    /**
     * @covers ::build
     */
    public function testBuildWithUnavailableOffers()
    {
        $collection = $this->offerCollectionFactory->create();

        $store = $this->storeManager->getStore();

        $result = $this->offerStockIndexHandler->build($collection, $store);

        $this->assertSame([], $result);
    }
}
