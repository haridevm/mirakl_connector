<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Helper;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Helper\Config as ConnectorConfig;

class Config extends ConnectorConfig
{
    // phpcs:disable
    public const XML_PATH_ENABLE_MCM                         = 'mirakl_mcm/import_product/enable_mcm';
    public const XML_PATH_MCM_PRODUCTS_IMPORT_MODE           = 'mirakl_mcm/import_product/mode';
    public const XML_PATH_MCM_ENABLE_PRODUCT_IMPORT          = 'mirakl_mcm/import_product/enable_product_import';
    public const XML_PATH_MCM_DEFAULT_VISIBILITY             = 'mirakl_mcm/import_product/default_visibility';
    public const XML_PATH_MCM_DEFAULT_TAX_CLASS              = 'mirakl_mcm/import_product/default_tax_class';
    public const XML_PATH_MCM_AUTO_ENABLE_PRODUCT            = 'mirakl_mcm/import_product/auto_enable_product';
    public const XML_PATH_ENABLE_ASYNC_MCM                   = 'mirakl_mcm/import_product_async/enable_mcm';
    public const XML_PATH_ASYNC_MCM_ENABLE_PRODUCT_IMPORT    = 'mirakl_mcm/import_product_async/enable_product_import';
    public const XML_PATH_ASYNC_MCM_DEFAULT_VISIBILITY       = 'mirakl_mcm/import_product_async/default_visibility';
    public const XML_PATH_ASYNC_MCM_DEFAULT_TAX_CLASS        = 'mirakl_mcm/import_product_async/default_tax_class';
    public const XML_PATH_ASYNC_MCM_AUTO_ENABLE_PRODUCT      = 'mirakl_mcm/import_product_async/auto_enable_product';
    public const XML_PATH_ENABLE_SYNC_MCM_PRODUCTS           = 'mirakl_sync/mcm_products/enable_mcm_products';
    public const XML_PATH_ENABLE_DELETE_MCM_PRODUCTS         = 'mirakl_sync/mcm_products_delete/enable';
    public const XML_PATH_MCM_ENABLE_INDEXING_IMPORT         = 'mirakl_mcm/product_import_indexing/enable_indexing_import';
    public const XML_PATH_MCM_ENABLE_INDEXING_IMPORT_AFTER   = 'mirakl_mcm/product_import_indexing/enable_indexing_import_after';
    public const XML_PATH_MCM_PRODUCT_EXPORT_CHUNK_SIZE      = 'mirakl_mcm/export_product/chunk_size';
    public const XML_PATH_MCM_IDENTIFIER_ATTRIBUTES          = 'mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes';
    // phpcs:enable

    /**
     * Returns default tax class for product import
     *
     * @return int
     */
    public function getDefaultTaxClass()
    {
        return (int) $this->getValue(self::XML_PATH_MCM_DEFAULT_TAX_CLASS);
    }

    /**
     * Returns default tax class for async product import
     *
     * @return int
     */
    public function getAsyncDefaultTaxClass()
    {
        return (int) $this->getValue(self::XML_PATH_ASYNC_MCM_DEFAULT_TAX_CLASS);
    }

    /**
     * Returns default visibility for product import
     *
     * @return int
     */
    public function getDefaultVisibility()
    {
        return (int) $this->getValue(self::XML_PATH_MCM_DEFAULT_VISIBILITY);
    }

    /**
     * Returns default visibility for async product import
     *
     * @return int
     */
    public function getAsyncDefaultVisibility()
    {
        return (int) $this->getValue(self::XML_PATH_ASYNC_MCM_DEFAULT_VISIBILITY);
    }

    /**
     * @return string
     */
    public function getProductsImportMode()
    {
        return $this->getValue(self::XML_PATH_MCM_PRODUCTS_IMPORT_MODE);
    }

    /**
     * Returns stores that allow product import
     *
     * @param bool $withDefault
     * @return StoreInterface[]
     */
    public function getStoresUsedForProductImport($withDefault = true)
    {
        $stores = [];
        foreach ($this->storeManager->getStores($withDefault) as $store) {
            if (!$store->getIsActive()) {
                continue;
            }
            if (
                ($this->isMcmEnabled() && $this->isProductImportEnabled($store))
                || ($this->isAsyncMcmEnabled() && $this->isAsyncProductImportEnabled($store))
            ) {
                $stores[] = $store;
            }
        }

        usort($stores, function (StoreInterface $a, StoreInterface $b) {
            return $a->getSortOrder() > $b->getSortOrder() ? 1 : -1;
        });

        return $stores;
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function isProductImportEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_MCM_ENABLE_PRODUCT_IMPORT, $store);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function isAsyncProductImportEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_ASYNC_MCM_ENABLE_PRODUCT_IMPORT, $store);
    }

    /**
     * @inheritdoc
     */
    public function getDeduplicationAttributes()
    {
        return [Data::CSV_MIRAKL_PRODUCT_ID];
    }

    /**
     * @return bool
     */
    public function isAutoEnableProduct()
    {
        return $this->getFlag(self::XML_PATH_MCM_AUTO_ENABLE_PRODUCT);
    }

    /**
     * @return bool
     */
    public function isAsyncAutoEnableProduct()
    {
        return $this->getFlag(self::XML_PATH_ASYNC_MCM_AUTO_ENABLE_PRODUCT);
    }

    /**
     * @return bool
     */
    public function isEnabledIndexingImport()
    {
        return $this->getFlag(self::XML_PATH_MCM_ENABLE_INDEXING_IMPORT);
    }

    /**
     * @return bool
     */
    public function isEnabledIndexingImportAfter()
    {
        return $this->getFlag(self::XML_PATH_MCM_ENABLE_INDEXING_IMPORT_AFTER);
    }

    /**
     * Returns true if MCM is enabled, false otherwise
     *
     * @return bool
     */
    public function isMcmEnabled()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_MCM);
    }

    /**
     * Returns true if MCM is enabled in async mode, false otherwise
     *
     * @return bool
     */
    public function isAsyncMcmEnabled()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_ASYNC_MCM);
    }

    /**
     * Returns true if MCM products deletion is enabled, false otherwise
     *
     * @return bool
     */
    public function isMcmProductsDeleteEnabled()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_DELETE_MCM_PRODUCTS);
    }

    /**
     * @return bool
     */
    public function isSyncMcmProducts()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_MCM_PRODUCTS);
    }

    /**
     * @return array
     */
    public function getMcmIdentifiersAttributes()
    {
        $value = $this->getValue(self::XML_PATH_MCM_IDENTIFIER_ATTRIBUTES);

        return !empty($value) ? explode(',', $value) : [];
    }

    /**
     * @return int
     */
    public function getMcmProductExportChunkSize()
    {
        return (int) $this->getValue(self::XML_PATH_MCM_PRODUCT_EXPORT_CHUNK_SIZE);
    }
}
