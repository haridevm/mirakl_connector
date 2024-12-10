<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

class LogisticClass implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$offer): void
    {
        if (isset($offer['logistic_class']) && is_array($offer['logistic_class'])) {
            $offer['logistic_class'] = $offer['logistic_class']['code'];
        }
    }
}
