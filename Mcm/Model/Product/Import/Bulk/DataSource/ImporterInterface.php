<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

interface ImporterInterface
{
    /**
     * @param DataSourceInterface $dataSource
     * @return bool
     */
    public function import(DataSourceInterface $dataSource): bool;

    /**
     * @return string
     */
    public function getOutput(): string;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return int
     */
    public function getExecutionTime(): int;
}