<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Indexer\Offer;

use Mirakl\OfferIndexer\Model\Indexer\Offer\Action;
use Mirakl\OfferIndexer\Test\Integration\Model\Indexer\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Indexer\Offer\Action
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ActionTest extends TestCase
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->action = $this->objectManager->create(Action::class);
    }

    /**
     * @covers ::executeRow
     * @covers ::execute
     * @covers ::getSkusByProductIds
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteRow()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $this->action->executeRow(1);

        $this->assertCount(4, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(1, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::executeList
     * @covers ::execute
     * @covers ::getSkusByProductIds
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteList()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $this->action->execute([486, 493]);

        $this->assertCount(2, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(3, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(4, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }

    /**
     * @covers ::executeFull
     * @magentoConfigFixture default/currency/options/base EUR
     */
    public function testExecuteFull()
    {
        $this->indexer->clear();

        $this->assertEmptyIndex();

        $this->action->executeFull();

        $this->assertCount(8, $this->fetchAll($this->offerIndexResource->getMainTable()));
        $this->assertCount(6, $this->fetchAll($this->offerProductIndexResource->getMainTable()));
        $this->assertCount(11, $this->fetchAll($this->offerStockIndexResource->getMainTable()));
    }
}
