<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer;

interface IndexerInterface
{
    /**
     * @param array $skus
     * @return void
     */
    public function clear(array $skus = []): void;

    /**
     * @param array $skus
     * @return void
     */
    public function execute(array $skus = []): void;
}
