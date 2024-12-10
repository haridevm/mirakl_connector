<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer\Collection\Customizer;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;

interface CustomizerInterface
{
    /**
     * @param OfferCollection $collection
     * @param StoreInterface $store
     * @return void
     */
    public function customize(OfferCollection $collection, StoreInterface $store): void;
}
