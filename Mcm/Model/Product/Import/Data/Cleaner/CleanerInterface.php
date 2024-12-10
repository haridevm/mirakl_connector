<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

interface CleanerInterface
{
    /**
     * @param array $data
     */
    public function clean(array &$data): void;
}