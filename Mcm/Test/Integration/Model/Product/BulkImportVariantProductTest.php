<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;
use Mirakl\Mcm\Helper\Data as McmDataHelper;

/**
 * @group MCM
 * @group CM51
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkImportVariantProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importVariantMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php

     * @param string $csv
     * @param array  $miraklProductIds
     * @param string $variantCode
     */
    public function testVariantProductMcmImport(string $csv, array $miraklProductIds, string $variantCode)
    {
        $process = $this->runImport($csv);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

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
            $this->assertEquals(
                $parentProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE),
                $variantCode
            );
        }
    }

    /**
     * @dataProvider importCreateParentVariantMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/single_mcm_product.php
     *
     * @param string $singleProductFile
     * @param string $variantProductFile
     * @param string $miraklProductId
     * @param string $variantCode
     */
    public function testCreateParentVariantProductMcmImport(
        string $singleProductFile,
        string $variantProductFile,
        string $miraklProductId,
        string $variantCode
    ) {
        // Import #1
        $process = $this->runImport($singleProductFile);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
        $this->assertNotNull($newProduct);
        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
        $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);

        // Import #2
        $process = $this->runImport($variantProductFile);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
        $this->assertNotNull($newProduct);
        $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
        $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);
        $this->assertEquals($newProduct->getData('name'), 'Slim Fit Polo');

        // Test parent creation
        $parentProduct = $this->coreHelper->getParentProduct($newProduct);
        $this->assertNotNull($parentProduct);
        $this->productResourceFactory->create()->load($parentProduct, $parentProduct->getId());
        $this->assertEquals($parentProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE), $variantCode);
    }

    /**
     * @dataProvider importUpdateVariantAlreadyPresentMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $updateVariantProductFile
     */
    public function testUpdateAlreadyVariantProductMcmImport(string $updateVariantProductFile)
    {
        $process = $this->runImport($updateVariantProductFile);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());
    }

    /**
     * @return array
     */
    public function importUpdateVariantAlreadyPresentMcmDataProvider(): array
    {
        return [
            ['CM51_update_single_product_variant_already_present.csv'],
        ];
    }

    /**
     * @return array
     */
    public function importVariantMcmDataProvider(): array
    {
        return [
            [
                'CM51_multi_variant_product.csv',
                ['abc5-4cf1-acdb-56152a77bc56', 'abc4-5cf1-acdb-56152a77bc56'],
                'variant_code'
            ],
        ];
    }

    /**
     * @return array
     */
    public function importCreateParentVariantMcmDataProvider(): array
    {
        return [
            [
                'CM51_single_product.csv',
                'CM51_create_parent_variant_product.csv',
                'abc5-4cf1-acdb-56152a77bc56',
                'variant_code'
            ],
        ];
    }
}
