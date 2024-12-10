<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

use Mirakl\Connector\Model\Offer\Import\Serializer\SerializerInterface;

class Prices implements FormatterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        if (isset($offer['price_ranges'])) {
            $offer['price_ranges'] = $this->serializer->serialize($offer['price_ranges']);
        }

        if (isset($offer['discount_ranges'])) {
            $offer['discount_ranges'] = $this->serializer->serialize($offer['discount_ranges']);
        }
    }
}
