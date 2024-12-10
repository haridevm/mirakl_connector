<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Offer\Collection;

use Mirakl\Connector\Model\ResourceModel\Offer\Collection;

class AddStoreFilterToCollection
{
    /**
     * @param Collection $collection
     * @param int $storeId
     * @return void
     */
    public function execute(Collection $collection, int $storeId): void
    {
        if (!$storeId) {
            return;
        }

        $collection->setStoreId($storeId);
        $collection->getSelect()
            ->join(
                ['offer_index' => $collection->getResource()->getTable('mirakl_offer_index')],
                'main_table.offer_id = offer_index.offer_id AND offer_index.store_id = ' . $storeId
            );
    }
}
