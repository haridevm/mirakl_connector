<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

class DiscountPriceProvider implements DiscountPriceProviderInterface
{
    /**
     * @var PriceValidatorInterface
     */
    private $priceValidator;

    /**
     * @param PriceValidatorInterface $priceValidator
     */
    public function __construct(PriceValidatorInterface $priceValidator)
    {
        $this->priceValidator = $priceValidator;
    }

    /**
     * @inheritdoc
     */
    public function get(array $price): ?float
    {
        $priceRanges = $price['volume_prices'] ?? null;

        if (empty($priceRanges)) {
            return null;
        }

        $discountPrice = null;
        foreach ($priceRanges as $priceRange) {
            if ($priceRange['quantity_threshold'] === 1 && isset($priceRange['unit_discount_price'])) {
                $discountPrice = $priceRange['unit_discount_price'];
                break;
            }
        }

        $startDate = $price['discount_start_date'] ?? null;
        $endDate = $price['discount_end_date'] ?? null;

        return $this->priceValidator->validate($discountPrice, $startDate, $endDate)
            ? $discountPrice
            : null;
    }
}