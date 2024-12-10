<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector;

use Mirakl\Connector\Model\Offer\Import\Serializer\SerializerInterface;
use Mirakl\Connector\Model\Offer\Import\Price\DiscountPriceProviderInterface;
use Mirakl\Connector\Model\Offer\Import\Price\PriceRangesProviderInterface;
use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

/**
 * Async price fields collector (API OF54 format)
 */
class PricesContext implements FieldCollectorInterface
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param DiscountPriceProviderInterface $discountPriceProvider
     * @param PriceRangesProviderInterface   $priceRangesProvider
     * @param SerializerInterface            $serializer
     */
    public function __construct(
        DiscountPriceProviderInterface $discountPriceProvider,
        PriceRangesProviderInterface $priceRangesProvider,
        SerializerInterface $serializer
    ) {
        $this->discountPriceProvider = $discountPriceProvider;
        $this->priceRangesProvider = $priceRangesProvider;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ChannelOfferInterface $offer): array
    {
        if (!$offer->getChannel()) {
            return [];
        }

        $info = $offer->getAdditionalInfo();
        $channelPrices = $this->getChannelPrices($offer->getChannel(), $info['prices'] ?? []);

        if (empty($channelPrices)) {
            return []; // No channel specific prices found
        }

        $data = [];
        $data['price'] = $channelPrices['origin_price'];
        $data['origin_price'] = $channelPrices['origin_price'];
        $data['discount_start_date'] = $channelPrices['discount_start_date'] ?? null;
        $data['discount_end_date'] = $channelPrices['discount_end_date'] ?? null;

        $discountPrice = $this->discountPriceProvider->get($channelPrices);
        $data['discount_price'] = $discountPrice;

        if ($discountPrice) {
            $data['price'] = $discountPrice;
            $data['final_price'] = $discountPrice;
        }

        $priceRanges = $this->priceRangesProvider->get($channelPrices, 'unit_origin_price');
        $discountRanges = $this->priceRangesProvider->get($channelPrices, 'unit_discount_price');

        $data['price_ranges'] = $this->serializer->serialize($priceRanges);
        $data['discount_ranges'] = $this->serializer->serialize($discountRanges);

        return $data;
    }

    /**
     * @param string $channel
     * @param array  $prices
     * @return array
     */
    private function getChannelPrices(string $channel, array $prices): array
    {
        foreach ($prices as $price) {
            $channels = $price['context']['channel_codes'] ?? [];
            if (in_array($channel, $channels)) {
                return $price; // Return specific channel prices
            }
        }

        return [];
    }
}
