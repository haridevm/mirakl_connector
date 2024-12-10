<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer\Collection\Customizer;

use Mirakl\OfferIndexer\Model\Indexer\Offer\Collection\Customizer\DefaultFilters;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Collection\Customizer\DefaultFilters
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class DefaultFiltersTest extends TestCase
{
    /**
     * @var DefaultFilters
     */
    private $defaultFilters;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultFilters = $this->objectManager->create(DefaultFilters::class);
    }

    /**
     * @covers ::customize
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testCustomize()
    {
        $collection = $this->offerCollectionFactory->create();
        $store = $this->storeManager->getStore();

        $this->assertCount(35, $collection->getAllIds());

        $this->defaultFilters->customize($collection, $store);

        $this->assertCount(8, $collection->getAllIds());
    }
}
