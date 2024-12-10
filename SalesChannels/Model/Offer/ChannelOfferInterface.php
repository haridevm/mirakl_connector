<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer;

interface ChannelOfferInterface
{
    /**
     * @return string|null
     */
    public function getChannel(): ?string;

    /**
     * @return bool
     */
    public function isAvailable(): bool;
}
