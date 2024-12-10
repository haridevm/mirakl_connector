<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer\Handler;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer\FinalPriceInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection;
use Mirakl\OfferIndexer\Model\ResourceModel\Offer\Index as OfferIndexResource;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;

class OfferIndexHandler implements IndexHandlerInterface
{
    /**
     * @var AvailabilityInterface
     */
    private $offerAvailability;

    /**
     * @var FinalPriceInterface
     */
    private $offerFinalPrice;

    /**
     * @var OfferIndexResource
     */
    private $offerIndexResource;

    /**
     * @param AvailabilityInterface $offerAvailability
     * @param FinalPriceInterface   $offerFinalPrice
     * @param OfferIndexResource    $offerIndexResource
     */
    public function __construct(
        AvailabilityInterface $offerAvailability,
        FinalPriceInterface $offerFinalPrice,
        OfferIndexResource $offerIndexResource
    ) {
        $this->offerAvailability = $offerAvailability;
        $this->offerFinalPrice = $offerFinalPrice;
        $this->offerIndexResource = $offerIndexResource;
    }

    /**
     * @inheritdoc
     */
    public function clear(array $skus = []): void
    {
        $this->offerIndexResource->clear($skus);
    }

    /**
     * @inheritdoc
     */
    public function update(array $data): void
    {
        $this->offerIndexResource->update($data);
    }

    /**
     * @inheritdoc
     */
    public function build(Collection $collection, StoreInterface $store): array
    {
        $result = [];

        /** @var \Mirakl\Connector\Model\Offer $offer */
        foreach ($collection as $offer) {
            if (!$this->offerAvailability->validate($offer, $store)) {
                continue;
            }

            $result[] = [
                'offer_id'            => (int) $offer->getId(),
                'store_id'            => (int) $store->getId(),
                'price'               => $offer->getPrice(),
                'origin_price'        => $offer->getOriginPrice(),
                'final_price'         => $this->offerFinalPrice->get($offer),
                'discount_price'      => $offer->getDiscountPrice() ?: null,
                'discount_start_date' => $offer->getDiscountStartDate() ?: null,
                'discount_end_date'   => $offer->getDiscountEndDate() ?: null,
                'price_ranges'        => $offer->getData('price_ranges') ?: null,
                'discount_ranges'     => $offer->getData('discount_ranges') ?: null,
            ];
        }

        return $result;
    }
}
