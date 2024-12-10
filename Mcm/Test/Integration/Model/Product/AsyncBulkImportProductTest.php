<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Mirakl\Mcm\Helper\Data as McmDataHelper;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter;
use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;

/**
 * @group MCM
 * @group CM54
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class AsyncBulkImportProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider asyncMcmImportDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $jsonFileName
     * @param array  $miraklProductIds
     */
    public function testMcmAsyncDataImport(string $jsonFileName, array $miraklProductIds)
    {
        $process = $this->runAsyncImport($jsonFileName);

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste',
            'name'               => 'Slim Fit Polo',
            'description'        => 'This ...',
            'color'              => '50',
            'ean  '              => '3570670000092',
            'size'               => '91',
            'material'           => '33,146',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_DISABLED,
        ];

        $this->validateAllProductValues($miraklProductIds, $values);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        /** @var \Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\Mcm $adapter */
        $adapter = $this->objectManager->get(EntityAdapter\Mcm::class);
        $this->assertCount(0, $adapter->getOldSku());
    }

    /**
     * @dataProvider asyncMcmImportDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 1
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $jsonFileName
     * @param array  $miraklProductIds
     */
    public function testEnableProductMcmAsyncImport(string $jsonFileName, array $miraklProductIds)
    {
        $this->runAsyncImport($jsonFileName);

        foreach ($miraklProductIds as $miraklProductId) {
            $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
            $this->assertInstanceOf(Product::class, $newProduct);
            $this->assertNotNull($newProduct);
            $this->assertEquals($miraklProductId, $newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID));
            $this->assertEquals(Status::STATUS_ENABLED, $newProduct->getStatus());
        }
    }

    /**
     * @return array
     */
    public function asyncMcmImportDataProvider(): array
    {
        return [
            ['CM54_single_product2.json', ['abc5-4cf1-acdb-56152a77bc65']],
        ];
    }

    /**
     * @dataProvider asyncImportDeduplicationMcmDataProvider
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/single_product.php
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_connector/product_attributes/mcm_synchronization/identifier_attributes ean
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/default_visibility 4
     *
     * @param string $updateJsonFileName
     * @param string $miraklProductId
     */
    public function testDeduplicationProductMcmAsyncImport(string $updateJsonFileName, string $miraklProductId)
    {
        $process = $this->runAsyncImport($updateJsonFileName);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
        $this->assertInstanceOf(Product::class, $newProduct);
        $this->assertNotNull($newProduct);
        $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste Update',
            'name'               => 'Slim Fit Polo UPDATE',
            'description'        => 'This ...UPDATE',
            'color'              => '54',
            'size'               => '167',
            'material'           => '33,146',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_ENABLED,
        ];

        // Check that tax class id did not change after update
        $this->assertEquals(0, $newProduct->getTaxClassId());

        $this->validateAllProductValues([$miraklProductId], $values);
    }

    /**
     * @return array
     */
    public function asyncImportDeduplicationMcmDataProvider(): array
    {
        return [
            ['CM54_single_product_update.json', 'abc5-4cf1-acdb-56152a77bc56'],
        ];
    }
}
