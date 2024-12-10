<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer\Handler;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Inventory\Store\StockIdResolver;
use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\StockIndex;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;

class StockIndexHandler implements IndexHandlerInterface
{
    /**
     * @var AvailabilityInterface
     */
    private $offerAvailability;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @var StockIndex
     */
    private $stockIndexResource;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private array $stockIds = [];

    /**
     * @param AvailabilityInterface    $offerAvailability
     * @param StockIdResolver          $stockIdResolver
     * @param StockIndex               $stockIndexResource
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        AvailabilityInterface $offerAvailability,
        StockIdResolver $stockIdResolver,
        StockIndex $stockIndexResource,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->offerAvailability = $offerAvailability;
        $this->stockIdResolver = $stockIdResolver;
        $this->stockIndexResource = $stockIndexResource;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(array $skus = []): void
    {
        $productIds = [];

        if (!empty($skus)) {
            $collection = $this->productCollectionFactory->create();
            /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
            $resource = $collection->getResource();
            $productIds = $resource->getProductsIdsBySkus($skus);
            if (empty($productIds)) {
                return;
            }
        }

        $this->stockIndexResource->clear($productIds);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data): void
    {
        $this->stockIndexResource->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function build(Collection $collection, StoreInterface $store): array
    {
        $result = [];

        /** @var \Mirakl\Connector\Model\Offer $offer */
        foreach ($collection as $offer) {
            if (!$this->offerAvailability->validate($offer, $store)) {
                continue;
            }

            $productId = $offer->getData('product_id');

            $result[$productId] = [
                'offer_id'   => $offer->getId(),
                'product_id' => $productId,
                'sku'        => $offer->getProductSku(),
                'stock_id'   => $this->getStockId((int) $store->getId()),
            ];
        }

        return $this->processParentProducts($result);
    }

    /**
     * @param int $storeId
     * @return int
     */
    private function getStockId(int $storeId): int
    {
        if (!isset($this->stockIds[$storeId])) {
            $this->stockIds[$storeId] = $this->stockIdResolver->resolve($storeId);
        }

        return $this->stockIds[$storeId];
    }

    /**
     * @param array $result
     * @return array
     */
    private function processParentProducts(array $result): array
    {
        $allParents = $this->productCollectionFactory->create()
            ->getParentProductsData(array_keys($result), [
                'parent_id'  => 'entity_id',
                'parent_sku' => 'sku',
            ]);

        foreach ($allParents as $childId => $parents) {
            foreach ($parents as $parent) {
                $result[$parent['parent_id']] = [
                    'offer_id'   => $result[$childId]['offer_id'],
                    'product_id' => $parent['parent_id'],
                    'sku'        => $parent['parent_sku'],
                    'stock_id'   => $result[$childId]['stock_id'],
                ];
            }
        }

        return $result;
    }
}
