<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector;

use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

interface FieldCollectorInterface
{
    /**
     * @param ChannelOfferInterface $offer
     * @return array
     */
    public function collect(ChannelOfferInterface $offer): array;
}
