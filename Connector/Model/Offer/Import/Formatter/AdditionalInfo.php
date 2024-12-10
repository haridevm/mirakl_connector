<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

use Mirakl\Connector\Model\Offer\Import\ChannelFields;
use Mirakl\Connector\Model\Offer\Import\Price\PriceBuilderInterface;

class AdditionalInfo implements FormatterInterface
{
    /**
     * @var PriceBuilderInterface
     */
    private $priceBuilder;

    /**
     * @var FormatterInterface
     */
    private $pricesFormatter;

    /**
     * @var ChannelFields
     */
    private $channelFields;

    /**
     * @param PriceBuilderInterface $priceBuilder
     * @param FormatterInterface    $pricesFormatter
     * @param ChannelFields         $channelFields
     */
    public function __construct(
        PriceBuilderInterface $priceBuilder,
        FormatterInterface $pricesFormatter,
        ChannelFields $channelFields
    ) {
        $this->priceBuilder = $priceBuilder;
        $this->pricesFormatter = $pricesFormatter;
        $this->channelFields = $channelFields;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        if (!is_array($offer['additional_info'])) {
            return;
        }

        $data = $offer['additional_info'];

        $this->buildLegacyFields($data);

        $offer['additional_info'] = json_encode($data);
    }

    /**
     * For retro-compatibility, this method builds legacy fields like API OF51.
     *
     * @param array $data
     * @return void
     */
    private function buildLegacyFields(array &$data): void
    {
        if (isset($data['fulfillment']['center']['code'])) {
            $data['fulfillment_center_code'] = $data['fulfillment']['center']['code'];
        }

        if (isset($data['measurement']['unit'])) {
            $data['measurement_units'] = $data['measurement']['unit'];
        }

        $prices = $data['prices'] ?? [];

        foreach ($prices as $price) {
            $channels = $price['context']['channel_codes'] ?? [];
            foreach ($channels as $channel) {
                $price = $this->priceBuilder->build($price);
                $this->pricesFormatter->format($price);
                foreach ($this->channelFields->get() as $field) {
                    if (isset($price[$field])) {
                        $data[sprintf('%s[channel=%s]', $field, $channel)] = $price[$field];
                    }
                }
            }
        }
    }
}