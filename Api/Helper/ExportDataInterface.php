<?php

declare(strict_types=1);

namespace Mirakl\Api\Helper;

interface ExportDataInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function export(array $data);
}
