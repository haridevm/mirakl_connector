<?php

declare(strict_types=1);

namespace Mirakl\Connector\Observer;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\IndexerFactory;
use Mirakl\Process\Model\Process;

class OfferImportAfterRefreshIndexObserver implements ObserverInterface
{
    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var string[]
     */
    private $indexers;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @param IndexerFactory         $indexerFactory
     * @param ProductResourceFactory $productResourceFactory
     * @param string[]               $indexers
     * @param int                    $chunkSize
     */
    public function __construct(
        IndexerFactory $indexerFactory,
        ProductResourceFactory $productResourceFactory,
        array $indexers = [],
        int $chunkSize = 200
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->productResource = $productResourceFactory->create();
        $this->indexers = $indexers;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $allSkus = $observer->getEvent()->getSkus();
        if (empty($allSkus)) {
            return;
        }

        $productIds = [];
        foreach (array_chunk($allSkus, $this->chunkSize) as $skus) {
            $productIds += $this->productResource->getProductsIdsBySkus($skus);
        }

        if (empty($productIds)) {
            return;
        }

        /** @var Process $process */
        $process = $observer->getEvent()->getProcess();

        $process->output(__('Updating index for products...'), true);
        ksort($this->indexers);

        foreach ($this->indexers as $indexerId) {
            try {
                /** @var Indexer $idx */
                $idx = $this->indexerFactory->create();
                $idx->load($indexerId);

                if ($idx->isScheduled()) {
                    $this->scheduleReindex($idx, $productIds);
                    $process->output(__('[OK] Scheduled %1', $indexerId));
                } elseif (!$idx->isWorking()) {
                    $start = microtime(true);
                    $idx->reindexList($productIds);
                    $duration = microtime(true) - $start;
                    $process->output(__('[OK] %1 (%2s)', $indexerId, round($duration, 2)));
                }
            } catch (\Exception $e) {
                $process->output(__('[ERROR] %1: %2', $indexerId, $e->getMessage()));
                // We must continue
            }
        }

        $process->output(__('Done!'));
    }

    /**
     * @param IndexerInterface $idx
     * @param array            $productIds
     * @return void
     */
    private function scheduleReindex(IndexerInterface $idx, array $productIds)
    {
        $connection = $this->productResource->getConnection();
        $changelog = $idx->getView()->getChangelog();
        $changelogTable = $this->productResource->getTable($changelog->getName());

        if ($connection->isTableExists($changelogTable)) {
            $connection->insertArray($changelogTable, [$changelog->getColumnName()], array_values($productIds));
        }
    }
}
