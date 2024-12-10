<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer\Handler;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection;

interface IndexHandlerInterface
{
    /**
     * @param array $skus
     * @return void
     */
    public function clear(array $skus = []): void;

    /**
     * @param array $data
     * @return void
     */
    public function update(array $data): void;

    /**
     * @param Collection     $collection
     * @param StoreInterface $store
     * @return array
     */
    public function build(Collection $collection, StoreInterface $store): array;
}
