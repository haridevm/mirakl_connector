<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mcm\Helper\Data as McmHelper;

class DataCleaner implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        // Ensure that internal fields are removed
        unset(
            $data['images'],
            $data['mirakl_category_id'],
            $data['category_ids'],
            $data['category_paths'],
            $data[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID],
            $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE],
            $data[McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER],
            $data['parent_id'],
            $data['parent_sku'],
            $data['parent_variant_group_code']
        );
    }
}
