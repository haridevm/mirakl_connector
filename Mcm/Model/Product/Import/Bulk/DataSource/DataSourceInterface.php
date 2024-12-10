<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

interface DataSourceInterface extends \IteratorAggregate
{
    /**
     * @return array|null
     */
    public function getNextBunch(): ?array;

    /**
     * @param array $data
     * @return int
     */
    public function write(array $data): int;

    /**
     * @return void
     */
    public function clean(): void;
}
