<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk;

/**
 * This class will bypass all custom options management during Mirakl MCM bulk import
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Option extends \Magento\CatalogImportExport\Model\Import\Product\Option
{
    /**
     * @inheritdoc
     */
    protected function _initSourceEntities(array $data)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _initStores(array $data)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _initTables(array $data)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _initMessageTemplates()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _importData()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function _initProductsSku()
    {
        $this->_productsSkuToId = [];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validateAmbiguousData()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        return true;
    }
}
