<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

interface LoaderInterface
{
    /**
     * @param Collection $collection
     * @return array
     */
    public function load(Collection $collection): array;
}