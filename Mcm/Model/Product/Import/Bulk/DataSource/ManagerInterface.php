<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

interface ManagerInterface
{
    /**
     * @param array $data
     */
    public function add(array $data): void;

    /**
     * @return DataSourceInterface
     */
    public function getDataSource(): DataSourceInterface;
}
