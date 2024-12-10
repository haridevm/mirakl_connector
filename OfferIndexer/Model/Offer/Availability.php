<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Offer;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer;

class Availability implements AvailabilityInterface
{
    /**
     * @var AvailabilityInterface[]
     */
    private array $validators;

    /**
     * @param array $validators
     */
    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(Offer $offer, StoreInterface $store): bool
    {
        foreach ($this->validators as $validator) {
            if (!$validator->validate($offer, $store)) {
                return false;
            }
        }

        return true;
    }
}
