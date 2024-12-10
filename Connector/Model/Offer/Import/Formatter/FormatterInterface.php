<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Formatter;

interface FormatterInterface
{
    /**
     * @param array $offer
     * @return void
     */
    public function format(array &$offer): void;
}
