<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Offer\Collection;

use Mirakl\Connector\Model\ResourceModel\Offer\Collection;
use Mirakl\OfferIndexer\Model\Offer\Collection\AddStoreFilterToCollection;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Offer\Collection\AddStoreFilterToCollection
 */
class AddStoreFilterToCollectionTest extends TestCase
{
    /**
     * @var AddStoreFilterToCollection
     */
    private $addStoreFilterToCollection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->addStoreFilterToCollection = $this->objectManager->create(AddStoreFilterToCollection::class);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $store = $this->storeManager->getStore();
        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);

        $this->assertNull($collection->getStoreId());

        $this->addStoreFilterToCollection->execute($collection, (int) $store->getId());

        $this->assertGreaterThan(0, $collection->getStoreId());
        $this->assertEquals($store->getId(), $collection->getStoreId());

        $from = $collection->getSelect()->getPart('from');

        $this->assertArrayHasKey('offer_index', $from);
        $this->assertSame('inner join', $from['offer_index']['joinType']);
        $this->assertSame($this->resource->getTableName('mirakl_offer_index'), $from['offer_index']['tableName']);
        $this->assertSame(
            'main_table.offer_id = offer_index.offer_id AND offer_index.store_id = ' . $store->getId(),
            $from['offer_index']['joinCondition']
        );
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithEmptyStoreId()
    {
        $collection = $this->objectManager->create(Collection::class);

        $this->assertNull($collection->getStoreId());

        $this->addStoreFilterToCollection->execute($collection, 0);

        $this->assertEquals(0, $collection->getStoreId());
    }
}
