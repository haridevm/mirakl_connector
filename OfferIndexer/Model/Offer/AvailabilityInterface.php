<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Offer;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer;

interface AvailabilityInterface
{
    /**
     * @param Offer          $offer
     * @param StoreInterface $store
     * @return bool
     */
    public function validate(Offer $offer, StoreInterface $store): bool;
}
