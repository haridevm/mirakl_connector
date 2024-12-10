<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;
use Mirakl\Mcm\Helper\Data as McmDataHelper;

/**
 * @group MCM
 * @group CM54
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AsyncBulkImportVariantProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider asyncImportVariantMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php

     * @param   string  $jsonFileName
     * @param   array   $miraklProductIds
     * @param   string  $variantCode
     */
    public function testVariantProductMcmAsyncImport(string $jsonFileName, array $miraklProductIds, string $variantCode)
    {
        $this->runAsyncImport($jsonFileName);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());

        foreach ($miraklProductIds as $miraklProductId) {
            $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
            $this->assertNotNull($newProduct);
            $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);
            $this->assertEquals($newProduct->getStatus(), Status::STATUS_DISABLED);

            // Test parent creation
            $parentProduct = $this->coreHelper->getParentProduct($newProduct);
            $this->assertNotNull($parentProduct);
            $this->productResource->load($parentProduct, $parentProduct->getId());
            $this->assertEquals($parentProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE), $variantCode);
        }
    }

    /**
     * @return  array
     */
    public function asyncImportVariantMcmDataProvider(): array
    {
        return [
            ['CM54_multi_variant_product.json', ['abc5-4cf1-acdb-56152a77bc56', 'abc4-5cf1-acdb-56152a77bc56'], 'variant_code'],
        ];
    }

    /**
     * @dataProvider asyncImportCreateParentVariantMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/single_mcm_product.php
     *
     * @param   string  $variantProductFile
     * @param   string  $miraklProductId
     * @param   string  $variantCode
     */
    public function testCreateParentVariantProductMcmAsyncImport(
        string $variantProductFile,
        string $miraklProductId,
        string $variantCode
    ) {
        $this->runAsyncImport($variantProductFile);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());

        // Test simple product update
        $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
        $this->assertNotNull($newProduct);
        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
        $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);
        $this->assertEquals($newProduct->getData('name'), 'Slim Fit Polo UPDATE');

        // Test parent product creation
        $parentProduct = $this->coreHelper->getParentProduct($newProduct);
        $this->assertNotNull($parentProduct);
        $this->productResourceFactory->create()->load($parentProduct, $parentProduct->getId());
        $this->assertEquals($parentProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE), $variantCode);
    }

    /**
     * @return  array
     */
    public function asyncImportCreateParentVariantMcmDataProvider(): array
    {
        return [
            ['CM54_create_parent_variant_product.json', 'abc5-4cf1-acdb-56152a77bc56', 'variant_code'],
        ];
    }

    /**
     * @dataProvider asyncImportUpdateVariantAlreadyPresentMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/single_mcm_product.php
     *
     * @param   string  $updateVariantProductFileName
     */
    public function testUpdateAlreadyVariantProductMcmAsyncImport(
        string $updateVariantProductFileName,
        string $miraklProductId,
        string $variantCode,
    ) {
        $this->runAsyncImport($updateVariantProductFileName);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());

        $updatedProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
        $this->assertEquals($updatedProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);
        $this->assertEquals($updatedProduct->getData('name'), 'Slim Fit Polo UPDATE');
        $this->assertEquals($updatedProduct->getData('description'), 'This ... UPDATE');
        $this->assertEquals($updatedProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE), $variantCode);
    }

    /**
     * @return  array
     */
    public function asyncImportUpdateVariantAlreadyPresentMcmDataProvider(): array
    {
        return [
            ['CM54_update_single_product_variant_already_present.json', 'abc5-4cf1-acdb-56152a77bc99', 'variant_code'],
        ];
    }
}