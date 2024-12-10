<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Customizer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirakl\Mcm\Helper\Data as McmHelper;

class VariantGroupCode implements CustomizerInterface
{
    /**
     * @inheritdoc
     */
    public function customize(Collection $collection): void
    {
        $collection->addFieldToSelect(McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE);
    }
}