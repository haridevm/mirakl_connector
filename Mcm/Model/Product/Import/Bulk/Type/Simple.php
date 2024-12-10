<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\Type;

class Simple extends \Magento\CatalogImportExport\Model\Import\Product\Type\Simple
{
    use TypeTrait;

    /**
     * @inheritdoc
     */
    protected function _addAttributeParams($attrSetName, array $attrParams, $attribute)
    {
        $attrParams = $this->_customizeAttributeParams($attrParams);

        return parent::_addAttributeParams($attrSetName, $attrParams, $attribute);
    }
}