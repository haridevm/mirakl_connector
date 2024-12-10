<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk;

class Validator extends \Magento\CatalogImportExport\Model\Import\Product\Validator
{
    /**
     * @inheritdoc
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData)
    {
        return true; // Do not validate attribute values
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        return true; // Do not validate attribute values
    }
}