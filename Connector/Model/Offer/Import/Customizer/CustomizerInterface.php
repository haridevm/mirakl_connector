<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import\Customizer;

interface CustomizerInterface
{
    /**
     * @param array $offer
     * @return void
     */
    public function customize(array &$offer): void;
}