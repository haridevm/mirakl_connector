<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer;

use Mirakl\Connector\Model\ResourceModel\Offer as OfferResource;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\Core\Model\ResourceModel\Shop as ShopResource;
use Mirakl\OfferIndexer\Model\Indexer\Offer\Indexer;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index as OfferIndexResource;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index as OfferProductIndexResource;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex as OfferStockIndexResource;
use PHPUnit\Framework\MockObject\MockObject;

class TestCase extends \Mirakl\OfferIndexer\Test\Integration\TestCase
{
    /**
     * @var ShopResource
     */
    protected $shopResource;

    /**
     * @var OfferResource
     */
    protected $offerResource;

    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var OfferIndexResource
     */
    protected $offerIndexResource;

    /**
     * @var OfferProductIndexResource
     */
    protected $offerProductIndexResource;

    /**
     * @var OfferStockIndexResource
     */
    protected $offerStockIndexResource;

    /**
     * @var OfferCollectionFactory
     */
    protected $offerCollectionFactory;

    /**
     * @var AvailabilityInterface|MockObject
     */
    protected $availabilityMock;

    /**
     * @inheritdoc
     * @magentoConfigFixture default/currency/options/base EUR
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->shopResource = $this->objectManager->get(ShopResource::class);
        $this->offerResource = $this->objectManager->get(OfferResource::class);
        $this->indexer = $this->objectManager->get(Indexer::class);
        $this->offerIndexResource = $this->objectManager->get(OfferIndexResource::class);
        $this->offerProductIndexResource = $this->objectManager->get(OfferProductIndexResource::class);
        $this->offerStockIndexResource = $this->objectManager->get(OfferStockIndexResource::class);
        $this->offerCollectionFactory = $this->objectManager->get(OfferCollectionFactory::class);
        $this->availabilityMock = $this->createMock(AvailabilityInterface::class);

        $this->importSampleShops();
        $this->importSampleOffers();
        $this->reindex();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->offerResource->getConnection()
            ->delete($this->offerResource->getMainTable());

        $this->shopResource->getConnection()
            ->delete($this->shopResource->getMainTable());
    }

    /**
     * @return string
     */
    protected function getFilesDir()
    {
        return realpath(__DIR__ . '/_files');
    }

    /**
     * @param string $table
     * @param array  $conds
     * @return array
     */
    protected function fetchAll(string $table, array $conds = []): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName($table));

        foreach ($conds as $cond => $value) {
            $select->where($cond, $value);
        }

        return $connection->fetchAll($select);
    }

    /**
     * @return void
     */
    protected function importSampleShops(): void
    {
        $shops = $this->getJsonFileContents('sample_shops.json');

        $this->shopResource->getConnection()
            ->insertOnDuplicate($this->shopResource->getMainTable(), $shops);
    }

    /**
     * @return void
     */
    protected function importSampleOffers(): void
    {
        $offers = $this->getJsonFileContents('sample_offers.json');

        $this->offerResource->getConnection()
            ->insertOnDuplicate($this->offerResource->getMainTable(), $offers);
    }

    /**
     * @return void
     */
    protected function reindex(): void
    {
        $this->assertCount(35, $this->fetchAll($this->offerResource->getMainTable()));

        $this->indexer->execute();
    }

    /**
     * @return void
     */
    protected function assertEmptyIndex(): void
    {
        $this->assertCount(0, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(0, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(0, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }
}
