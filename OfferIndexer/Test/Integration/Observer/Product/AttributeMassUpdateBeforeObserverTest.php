<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Observer\Product;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexProcessor;
use Mirakl\OfferIndexer\Observer\Product\AttributeMassUpdateBeforeObserver;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group observer
 * @coversDefaultClass \Mirakl\OfferIndexer\Observer\Product\AttributeMassUpdateBeforeObserver
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class AttributeMassUpdateBeforeObserverTest extends TestCase
{
    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecute()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product_ids' => [1],
                'attributes_data' => [
                    'status' => 'foo',
                ],
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(AttributeMassUpdateBeforeObserver::class);
        $observer->execute($eventObserver);

        $this->assertCount(4, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(1, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteDoesNothing()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product_ids' => [999],
                'attributes_data' => [
                    'foo' => 'bar',
                ],
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $indexProcessorMock = $this->createMock(IndexProcessor::class);
        $indexProcessorMock->expects($this->never())
            ->method('reindexList');

        $observer = $this->objectManager->create(AttributeMassUpdateBeforeObserver::class, [
            'indexProcessor' => $indexProcessorMock,
        ]);
        $observer->execute($eventObserver);

        $this->assertEmptyIndex();
    }
}
