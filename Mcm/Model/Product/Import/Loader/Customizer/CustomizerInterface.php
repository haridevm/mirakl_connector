<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Customizer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

interface CustomizerInterface
{
    /**
     * @param Collection $collection
     */
    public function customize(Collection $collection): void;
}