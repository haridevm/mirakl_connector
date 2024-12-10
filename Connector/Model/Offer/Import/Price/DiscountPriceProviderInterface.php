<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

interface DiscountPriceProviderInterface
{
    /**
     * @param array $price
     * @return float|null
     */
    public function get(array $price): ?float;
}
