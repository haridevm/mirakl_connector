<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer\Channel;

use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

interface DataOverriderInterface
{
    /**
     * @param ChannelOfferInterface $offer
     * @return void
     */
    public function override(ChannelOfferInterface $offer): void;
}
