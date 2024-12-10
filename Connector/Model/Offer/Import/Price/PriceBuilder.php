<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

class PriceBuilder implements PriceBuilderInterface
{
    /**
     * @var DiscountPriceProviderInterface
     */
    private $discountPriceProvider;

    /**
     * @var PriceRangesProviderInterface
     */
    private $priceRangesProvider;

    /**
     * @param DiscountPriceProviderInterface $discountPriceProvider
     * @param PriceRangesProviderInterface   $priceRangesProvider
     */
    public function __construct(
        DiscountPriceProviderInterface $discountPriceProvider,
        PriceRangesProviderInterface $priceRangesProvider
    ) {
        $this->discountPriceProvider = $discountPriceProvider;
        $this->priceRangesProvider = $priceRangesProvider;
    }

    /**
     * @inheritdoc
     */
    public function build(array $price): array
    {
        $data = [];

        $data['price'] = $price['origin_price'];
        $data['origin_price'] = $price['origin_price'];
        $data['discount_price'] = $price['discount_price'] ?? null;
        $data['discount_start_date'] = $price['discount_start_date'] ?? null;
        $data['discount_end_date'] = $price['discount_end_date'] ?? null;

        if ($discountPrice = $this->discountPriceProvider->get($price)) {
            $data['price'] = $discountPrice;
            $data['discount_price'] = $discountPrice;
        }

        $data['price_ranges'] = $this->priceRangesProvider->get($price, 'unit_origin_price');
        $data['discount_ranges'] = $this->priceRangesProvider->get($price, 'unit_discount_price');

        return $data;
    }
}
