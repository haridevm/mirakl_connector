<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\OfferIndexer\Model\Indexer\Offer\Indexer;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Indexer
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexerTest extends TestCase
{
    /**
     * @covers ::clear
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testClearAll()
    {
        $this->assertCount(8, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(6, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(11, $this->fetchAll($this->offerStockIndexResource->getMainTable()));

        $this->indexer->clear();

        $this->assertEmptyIndex();
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecute()
    {
        $this->testClearAll();

        $this->indexer->execute();

        $this->assertCount(8, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(6, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(11, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteWithSkus()
    {
        $this->testClearAll();

        $this->indexer->execute(['MS11-L-Blue', 'MS11-XL-Yellow']);

        $this->assertCount(2, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(3, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteWithInactiveStore()
    {
        $this->testClearAll();

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getIsActive')
            ->willReturn(false);

        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        $indexer = $this->objectManager->create(Indexer::class, [
            'storeManager' => $storeManagerMock,
        ]);

        $indexer->execute();

        $this->assertCount(0, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(0, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(0, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }
}
