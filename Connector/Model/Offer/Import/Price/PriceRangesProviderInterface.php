<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

interface PriceRangesProviderInterface
{
    /**
     * @param array  $price
     * @param string $field
     * @return array
     */
    public function get(array $price, string $field): array;
}
