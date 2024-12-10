<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Customizer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class DefaultColumns implements CustomizerInterface
{
    /**
     * @inheritdoc
     */
    public function customize(Collection $collection): void
    {
        $collection->getSelect()->columns(['entity_id', 'sku', 'type_id', 'attribute_set_id']);
    }
}