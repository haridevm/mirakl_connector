<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer;

use Mirakl\Connector\Model\Offer;

class ChannelOffer extends Offer implements ChannelOfferInterface
{
    /**
     * @inheritdoc
     */
    public function getChannel(): ?string
    {
        return $this->_data['channel'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return !$this->getChannel() || in_array($this->getChannel(), $this->getActiveChannels());
    }
}
