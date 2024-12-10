<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\DB\Trigger\Creator;

interface TriggerCreatorInterface
{
    /**
     * @param int $stockId
     * @return string
     */
    public function create(int $stockId): string;
}
