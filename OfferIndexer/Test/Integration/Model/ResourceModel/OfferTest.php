<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\ResourceModel;

use Mirakl\Connector\Model\Offer;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer as OfferResource;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\ResourceModel\Offer
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class OfferTest extends TestCase
{
    /**
     * @var OfferResource
     */
    private $offerResourceModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->offerResourceModel = $this->objectManager->create(OfferResource::class);
    }

    /**
     * @covers ::_getLoadSelect
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testGetLoadSelect()
    {
        $store = $this->storeManager->getStore();
        $offer = $this->objectManager->create(Offer::class);
        $offer->setStoreId($store->getId());

        $this->offerResourceModel->load($offer, 2259);

        $this->assertEquals(2259, $offer->getId());
    }
}
