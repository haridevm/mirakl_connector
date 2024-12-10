<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;

class Availability implements AvailabilityInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Offer $offer, StoreInterface $store): bool
    {
        if (!$offer instanceof ChannelOfferInterface) {
            return true;
        }

        return $offer->isAvailable();
    }
}
