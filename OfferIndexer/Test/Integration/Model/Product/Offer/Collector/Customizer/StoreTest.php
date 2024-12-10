<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Product\Offer\Collector\Customizer;

use Magento\Catalog\Model\Product;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\OfferIndexer\Model\Product\Offer\Collector\Customizer\Store as StoreCustomizer;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Product\Offer\Collector\Customizer\Store
 * @covers ::__construct
 */
class StoreTest extends TestCase
{
    /**
     * @var StoreCustomizer
     */
    private $storeCustomizer;

    /**
     * @var OfferCollectionFactory
     */
    private $offerCollectionFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeCustomizer = $this->objectManager->create(StoreCustomizer::class);
        $this->offerCollectionFactory = $this->objectManager->create(OfferCollectionFactory::class);
    }

    /**
     * @covers ::customize
     */
    public function testCustomize()
    {
        $productMock = $this->createMock(Product::class);
        $collection = $this->offerCollectionFactory->create();

        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->assertNull($collection->getStoreId());

        $this->storeCustomizer->customize($productMock, $collection);

        $this->assertSame(1, $collection->getStoreId());
    }
}
