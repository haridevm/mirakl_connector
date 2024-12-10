<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Collector;

interface CollectorInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function collect(array $data): array;
}