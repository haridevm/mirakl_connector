<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer;

use Mirakl\Connector\Model\Offer;

interface FinalPriceInterface
{
    /**
     * @param Offer $offer
     * @param int|null $qty
     * @return float
     */
    public function get(Offer $offer, ?int $qty = null): float;
}
