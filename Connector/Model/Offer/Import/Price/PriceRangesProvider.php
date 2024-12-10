<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Price;

class PriceRangesProvider implements PriceRangesProviderInterface
{
    /**
     * @var string
     */
    private string $priceFormat;

    /**
     * @param string $priceFormat
     */
    public function __construct(string $priceFormat = '%.2F')
    {
        $this->priceFormat = $priceFormat;
    }

    /**
     * @inheritdoc
     */
    public function get(array $price, string $field): array
    {
        if (!isset($price['volume_prices'])) {
            return [];
        }

        $priceRanges = [];
        foreach ($price['volume_prices'] as $volumePrice) {
            if (isset($volumePrice[$field])) {
                $priceRanges[] = [
                    'qty'   => $volumePrice['quantity_threshold'],
                    'price' => sprintf($this->priceFormat, $volumePrice[$field]),
                ];
            }
        }

        return $priceRanges;
    }
}
