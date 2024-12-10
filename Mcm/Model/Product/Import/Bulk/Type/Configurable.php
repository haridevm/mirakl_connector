<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\Type;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Configurable extends \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable
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
