<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mcm\Helper\Data as McmHelper;

class VariantGroupCode implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        if (!empty($data['parent_sku'])) {
            if (empty($data['parent_variant_group_code'])) {
                $data[McmHelper::CSV_MIRAKL_VARIANT_GROUP_CODE] = $data['parent_sku'];
            } else {
                $data[McmHelper::CSV_MIRAKL_VARIANT_GROUP_CODE] = $data['parent_variant_group_code'];
            }
        } elseif (array_key_exists(McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE, $data)) {
            $data[McmHelper::CSV_MIRAKL_VARIANT_GROUP_CODE] = $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE];
        }
    }
}
