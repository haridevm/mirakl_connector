<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

interface PriceValidatorInterface
{
    /**
     * @param float $price
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $useDate
     * @return bool
     */
    public function validate($price, $startDate = null, $endDate = null, $useDate = null): bool;
}