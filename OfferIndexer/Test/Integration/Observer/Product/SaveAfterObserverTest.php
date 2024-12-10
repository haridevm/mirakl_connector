<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Observer\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexProcessor;
use Mirakl\OfferIndexer\Observer\Product\SaveAfterObserver;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group observer
 * @coversDefaultClass \Mirakl\OfferIndexer\Observer\Product\SaveAfterObserver
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class SaveAfterObserverTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteWithSimpleProduct()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        // Load simple product
        $product = $this->productRepository->getById(1); // 24-MB01

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product' => $product,
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(SaveAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertCount(4, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(1, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteWithConfigurableProduct()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        // Load configurable product
        $product = $this->productRepository->get('MS11');

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product' => $product,
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $observer = $this->objectManager->create(SaveAfterObserver::class);
        $observer->execute($eventObserver);

        $this->assertCount(3, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(6, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::execute
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteDoesNothing()
    {
        $productMock = $this->createMock(Product::class);

        $event = $this->objectManager->create(Event::class, [
            'data' => [
                'product' => $productMock,
            ]
        ]);

        $eventObserver = $this->objectManager->create(Observer::class);
        $eventObserver->setEvent($event);

        $indexProcessorMock = $this->createMock(IndexProcessor::class);
        $indexProcessorMock->expects($this->never())
            ->method('reindexRow');
        $indexProcessorMock->expects($this->never())
            ->method('reindexList');

        $observer = $this->objectManager->create(SaveAfterObserver::class, [
            'indexProcessor' => $indexProcessorMock,
        ]);
        $observer->execute($eventObserver);
    }
}
