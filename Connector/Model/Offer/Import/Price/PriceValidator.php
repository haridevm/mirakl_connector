<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

class PriceValidator implements PriceValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($price, $startDate = null, $endDate = null, $useDate = null): bool
    {
        $price = (float) $price;

        if (!$price) {
            return false;
        }

        if (!$startDate && !$endDate) {
            return true;
        }

        $from = $this->getDate($startDate);
        $to = $this->getDate($endDate);
        $current = $this->getDate($useDate ?: 'now');

        if (!$from) {
            return $current <= $to;
        } elseif (!$to) {
            return $current >= $from;
        }

        return $current >= $from && $current <= $to;
    }

    /**
     * @param mixed $date
     * @return \DateTime|null
     */
    private function getDate($date): ?\DateTime
    {
        if ($date instanceof \DateTime) {
            return $date;
        }

        return is_string($date) ? new \DateTime($date) : null;
    }
}