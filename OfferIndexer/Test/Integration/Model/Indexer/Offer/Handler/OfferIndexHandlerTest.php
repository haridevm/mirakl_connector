<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer\Handler;

use Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\OfferIndexHandler;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\OfferIndexHandler
 * @covers ::__construct
 * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::_construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class OfferIndexHandlerTest extends TestCase
{
    /**
     * @var OfferIndexHandler
     */
    private $offerIndexHandler;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->offerIndexHandler = $this->objectManager->create(OfferIndexHandler::class, [
            'offerAvailability' => $this->availabilityMock,
        ]);
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::deleteBySkus
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearPartial()
    {
        $this->offerIndexHandler->clear(['24-MB01']);

        $this->assertCount(4, $this->fetchAll($this->offerIndexResource->getMainTable()));
    }

    /**
     * @covers ::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::clear
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::truncate
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearAll()
    {
        $this->offerIndexHandler->clear();

        $this->assertCount(0, $this->fetchAll($this->offerIndexResource->getMainTable()));
    }

    /**
     * @covers ::update
     * @covers \Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index::update
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testUpdate()
    {
        $store = $this->storeManager->getStore();

        $this->offerIndexHandler->update([
            [
                'offer_id'    => '2231',
                'store_id'    => $store->getId(),
                'final_price' => 99.99,
            ],
        ]);

        $rows = $this->fetchAll($this->offerIndexResource->getMainTable(), ['offer_id = ?' => '2231']);
        $this->assertEquals(99.99, $rows[0]['final_price']);
    }

    /**
     * @covers ::build
     */
    public function testBuild()
    {
        $this->availabilityMock->expects($this->exactly(2))
            ->method('validate')
            ->willReturn(true);

        $collection = $this->offerCollectionFactory->create();
        $collection->addFieldToFilter('offer_id', ['in' => ['2231', '2237']]);

        $store = $this->storeManager->getStore();

        $result = $this->offerIndexHandler->build($collection, $store);

        $this->assertEquals([
            [
                'offer_id'            => '2231',
                'store_id'            => $store->getId(),
                'price'               => '58.00',
                'origin_price'        => '60.00',
                'final_price'         => '58.00',
                'discount_price'      => '58.00',
                'discount_start_date' => '2023-08-14 22:00:00',
                'discount_end_date'   => '0000-00-00 00:00:00',
                'price_ranges'        => '1|60.00',
                'discount_ranges'     => '1|58.00',
            ],
            [
                'offer_id'            => '2237',
                'store_id'            => $store->getId(),
                'price'               => '12.90',
                'origin_price'        => '12.90',
                'final_price'         => '12.90',
                'discount_price'      => '0.00',
                'discount_start_date' => '0000-00-00 00:00:00',
                'discount_end_date'   => '0000-00-00 00:00:00',
                'price_ranges'        => '1|12.90',
                'discount_ranges'     => null,
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

        $result = $this->offerIndexHandler->build($collection, $store);

        $this->assertSame([], $result);
    }
}
