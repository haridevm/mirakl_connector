<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Customizer;

use Mirakl\Connector\Model\Offer\Import\Price\PriceBuilderInterface;

class Prices implements CustomizerInterface
{
    /**
     * @var PriceBuilderInterface
     */
    private $priceBuilder;

    /**
     * @param PriceBuilderInterface $priceBuilder
     */
    public function __construct(PriceBuilderInterface $priceBuilder)
    {
        $this->priceBuilder = $priceBuilder;
    }

    /**
     * @inheritdoc
     */
    public function customize(array &$offer): void
    {
        $prices = $offer['prices'] ?? [];

        foreach ($prices as $price) {
            if (!isset($price['context'])) {
                break; // When 'context' is undefined, it is default price
            }
        }

        if (empty($price)) {
            return; // Should not happen
        }

        $offer = array_merge($offer, $this->priceBuilder->build($price));

        if (count($offer['discount_ranges']) > 1) {
            // Keep retro-compatibility with legacy import (API OF51)
            unset($offer['discount_price']);
        }

        $offer['total_price'] = $offer['price'] + ($offer['min_shipping_price'] ?? 0);
    }
}