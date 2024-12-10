<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer\Handler;

use Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\ProductIndexHandler;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\ProductIndexHandler
 * @covers ::__construct
 * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index::_construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ProductIndexHandlerTest extends TestCase
{
    /**
     * @var ProductIndexHandler
     */
    private $offerProductIndexHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->offerProductIndexHandler = $this->objectManager->create(ProductIndexHandler::class, [
            'offerAvailability' => $this->availabilityMock,
        ]);
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index::clear
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearPartial()
    {
        $this->offerProductIndexHandler->clear(['24-MB01']);

        $this->assertCount(5, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index::truncate
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearAll()
    {
        $this->offerProductIndexHandler->clear();

        $this->assertCount(0, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
    }

    /**
     * @covers ::update
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index::update
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testUpdate()
    {
        $store = $this->storeManager->getStore();

        $this->offerProductIndexHandler->update([
            [
                'sku'       => '24-MB01',
                'store_id'  => $store->getId(),
                'min_price' => 5.99,
                'max_price' => 99.99,
            ],
        ]);

        $rows = $this->fetchAll($this->offerProductIndexResource->getMainTable(), ['sku = ?' => '24-MB01']);

        $this->assertEquals(5.99, $rows[0]['min_price']);
        $this->assertEquals(99.99, $rows[0]['max_price']);
    }

    /**
     * @covers ::build
     * @covers ::processParentProducts
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testBuild()
    {
        $this->availabilityMock->expects($this->exactly(4))
            ->method('validate')
            ->willReturn(true);

        $collection = $this->offerCollectionFactory->create();
        $collection->addFieldToFilter('offer_id', ['in' => ['2231', '2259', '2260', '2261']]);

        $store = $this->storeManager->getStore();

        $result = $this->offerProductIndexHandler->build($collection, $store);

        $this->assertEquals([
            '24-MB01' => [
                'sku'       => '24-MB01',
                'store_id'  => $store->getId(),
                'min_price' => '58',
                'max_price' => '58',
            ],
            'MS11-L-Blue' => [
                'sku'       => 'MS11-L-Blue',
                'store_id'  => $store->getId(),
                'min_price' => '19.90',
                'max_price' => '19.90',
            ],
            'MS11-XL-Yellow' => [
                'sku'       => 'MS11-XL-Yellow',
                'store_id'  => $store->getId(),
                'min_price' => '12.40',
                'max_price' => '12.40',
            ],
            'MS11-M-Green' => [
                'sku'       => 'MS11-M-Green',
                'store_id'  => $store->getId(),
                'min_price' => '27.90',
                'max_price' => '27.90',
            ],
            'MS11' => [
                'sku'       => 'MS11',
                'store_id'  => $store->getId(),
                'min_price' => '12.40',
                'max_price' => '27.90',
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

        $result = $this->offerProductIndexHandler->build($collection, $store);

        $this->assertSame([], $result);
    }
}
