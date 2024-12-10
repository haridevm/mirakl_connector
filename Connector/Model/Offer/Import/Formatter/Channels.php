<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

class Channels implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        if (isset($offer['channels']) && is_array($offer['channels'])) {
            $offer['channels'] = implode('|', $offer['channels']);
        }
    }
}
