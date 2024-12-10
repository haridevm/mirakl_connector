<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\Store\Api\Data\StoreInterface;

interface FormatterInterface
{
    /**
     * @param array               $data
     * @param StoreInterface|null $store
     */
    public function format(array &$data, ?StoreInterface $store = null): void;
}
