<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

interface PriceBuilderInterface
{
    /**
     * @param array $price
     * @return array
     */
    public function build(array $price): array;
}