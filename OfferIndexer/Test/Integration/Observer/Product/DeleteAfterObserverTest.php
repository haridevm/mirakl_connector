<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexerInterface;
use Mirakl\OfferIndexer\Observer\Product\DeleteAfterObserver;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group observer
 * @coversDefaultClass \Mirakl\OfferIndexer\Observer\Product\DeleteAfterObserver
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class DeleteAfterObserverTest extends TestCase
{
    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecute()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $this->indexer->execute(['24-MB01']);

        $this->assertCount(4, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(1, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getSku')
            ->willReturn('24-MB01');

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product' => $productMock,
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(DeleteAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertEmptyIndex();
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteDoesNothing()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $productMock = $this->createMock(Product::class);

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product' => $productMock,
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $indexerMock = $this->createMock(IndexerInterface::class);
        $indexerMock->expects($this->never())
            ->method('clear');

        $observer = $this->objectManager->create(DeleteAfterObserver::class, [
            'indexer' => $indexerMock,
        ]);
        $observer->execute($eventObserver);

        $this->assertEmptyIndex();
    }
}
