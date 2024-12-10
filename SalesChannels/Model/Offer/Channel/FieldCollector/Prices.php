<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector;

use Mirakl\Connector\Model\Offer\Import\ChannelFields;
use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

/**
 * Legacy price fields collector (API OF51 format)
 */
class Prices implements FieldCollectorInterface
{
    /**
     * @var ChannelFields
     */
    private $channelFields;

    /**
     * @param ChannelFields $channelFields
     */
    public function __construct(ChannelFields $channelFields)
    {
        $this->channelFields = $channelFields;
    }

    /**
     * @inheritdoc
     */
    public function collect(ChannelOfferInterface $offer): array
    {
        if (!$offer->getChannel()) {
            return [];
        }

        $data = [];
        $info = $offer->getAdditionalInfo();

        foreach ($this->channelFields->get() as $field) {
            $channelField = sprintf('%s[channel=%s]', $field, $offer->getChannel());
            if (isset($info[$channelField]) && '' !== $info[$channelField]) {
                $data[$field] = $info[$channelField];
            }
        }

        return $data;
    }
}
