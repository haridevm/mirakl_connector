<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Indexer;
use Magento\Framework\Mview;

class Action implements Indexer\ActionInterface, Mview\ActionInterface
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @param ProductResource  $productResource
     * @param IndexerInterface $indexer
     */
    public function __construct(
        ProductResource $productResource,
        IndexerInterface $indexer
    ) {
        $this->productResource = $productResource;
        $this->indexer = $indexer;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->indexer->execute();
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        $skus = $this->getSkusByProductIds($ids);
        if (!empty($skus)) {
            $this->indexer->execute($skus);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    /**
     * @param array $ids
     * @return string[]
     */
    private function getSkusByProductIds(array $ids): array
    {
        return array_column($this->productResource->getProductsSku($ids), 'sku');
    }
}
