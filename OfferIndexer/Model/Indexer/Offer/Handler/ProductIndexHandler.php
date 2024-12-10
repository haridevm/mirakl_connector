<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer\Handler;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer\FinalPriceInterface;
use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product\Index as OfferProductIndexResource;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;

class ProductIndexHandler implements IndexHandlerInterface
{
    /**
     * @var AvailabilityInterface
     */
    private $offerAvailability;

    /**
     * @var FinalPriceInterface
     */
    private $offerFinalPrice;

    /**
     * @var OfferProductIndexResource
     */
    private $offerProductIndexResource;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param AvailabilityInterface     $offerAvailability
     * @param FinalPriceInterface       $offerFinalPrice
     * @param OfferProductIndexResource $offerProductIndexResource
     * @param ProductCollectionFactory  $productCollectionFactory
     */
    public function __construct(
        AvailabilityInterface $offerAvailability,
        FinalPriceInterface $offerFinalPrice,
        OfferProductIndexResource $offerProductIndexResource,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->offerAvailability = $offerAvailability;
        $this->offerFinalPrice = $offerFinalPrice;
        $this->offerProductIndexResource = $offerProductIndexResource;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function clear(array $skus = []): void
    {
        $this->offerProductIndexResource->clear($skus);
    }

    /**
     * @inheritdoc
     */
    public function update(array $data): void
    {
        $this->offerProductIndexResource->update($data);
    }

    /**
     * @inheritdoc
     */
    public function build(Collection $collection, StoreInterface $store): array
    {
        $result = [];

        /** @var \Mirakl\Connector\Model\Offer $offer */
        foreach ($collection as $offer) {
            if (!$this->offerAvailability->validate($offer, $store)) {
                continue;
            }

            $sku = $offer->getProductSku();
            $price = $this->offerFinalPrice->get($offer);

            if (!isset($result[$sku])) {
                $result[$sku] = [
                    'sku'       => $sku,
                    'store_id'  => (int) $store->getId(),
                    'min_price' => $price,
                    'max_price' => $price,
                ];
            } else {
                if ($price < $result[$sku]['min_price']) {
                    $result[$sku]['min_price'] = $price;
                }
                if ($price > $result[$sku]['max_price']) {
                    $result[$sku]['max_price'] = $price;
                }
            }
        }

        return $this->processParentProducts($result);
    }

    /**
     * @param array $result
     * @return array
     */
    private function processParentProducts(array $result): array
    {
        $allParentSkus = $this->productCollectionFactory->create()
            ->getParentProductSkus(array_keys($result));

        foreach ($allParentSkus as $childSku => $parentSkus) {
            foreach ($parentSkus as $parentSku) {
                if (!isset($result[$parentSku])) {
                    $result[$parentSku] = [
                        'sku'       => $parentSku,
                        'store_id'  => $result[$childSku]['store_id'],
                        'min_price' => $result[$childSku]['min_price'],
                        'max_price' => $result[$childSku]['max_price'],
                    ];
                } else {
                    if ($result[$childSku]['min_price'] < $result[$parentSku]['min_price']) {
                        $result[$parentSku]['min_price'] = $result[$childSku]['min_price'];
                    }
                    if ($result[$childSku]['max_price'] > $result[$parentSku]['max_price']) {
                        $result[$parentSku]['max_price'] = $result[$childSku]['max_price'];
                    }
                }
            }
        }

        return $result;
    }
}
