<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\Import;

interface DataBuilderInterface
{
    /**
     * @param array $offer
     * @return array
     */
    public function build(array $offer): array;
}