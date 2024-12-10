<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer;

use Mirakl\Connector\Model\Offer;

class FinalPrice implements FinalPriceInterface
{
    /**
     * @param Offer $offer
     * @param int|null $qty
     * @return float
     */
    public function get(Offer $offer, ?int $qty = null): float
    {
        $price = (float) $offer->getPrice();
        $discountPrice = 0;

        if ($qty && $offer->isDiscountPriceValid()) {
            // Check if a discount price is valid for current quantity
            $discount = $offer->getDiscount();
            if ($ranges = $discount->getRanges()) {
                /** @var \Mirakl\MMP\Common\Domain\DiscountRange $range */
                foreach (array_reverse($ranges->getItems()) as $range) {
                    if ($qty >= $range->getQuantityThreshold()) {
                        $discountPrice = (float) $range->getPrice();
                        break;
                    }
                }
            }
        }

        if ($qty > 1) {
            // Check if a price range is valid for current quantity
            $ranges = $offer->getPriceRanges();
            foreach (array_reverse($ranges->getItems()) as $range) {
                if ($qty >= $range->getQuantityThreshold() && $range->getPrice() <= $price) {
                    $price = (float) $range->getPrice();
                    break;
                }
            }
        }

        return ($discountPrice > 0 && $discountPrice <= $price) ? $discountPrice : $price;
    }
}
