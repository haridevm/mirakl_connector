<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Builder;

interface LoaderInterface
{
    /**
     * @param array $values
     * @return array
     */
    public function load(array $values = []): array;
}