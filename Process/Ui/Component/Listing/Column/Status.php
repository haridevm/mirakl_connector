<?php

declare(strict_types=1);

namespace Mirakl\Process\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Mirakl\Process\Model\Process;

class Status extends Column
{
    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                if ($row[$this->getName()]) {
                    $statusClass = Process::getClassForStatus($row[$this->getName()]);
                    $row[$this->getName()] = '<span class="' . $statusClass . '">'
                        . '<span>' . __($row[$this->getName()]) . '</span></span>';
                }
            }
            unset($row);
        }

        return $dataSource;
    }
}
