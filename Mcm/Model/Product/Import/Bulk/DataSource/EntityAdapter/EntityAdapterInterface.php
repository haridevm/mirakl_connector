<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter;

use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\DataSourceInterface;

interface EntityAdapterInterface
{
    /**
     * @param DataSourceInterface $dataSource
     */
    public function setDataSource(DataSourceInterface $dataSource): void;
}