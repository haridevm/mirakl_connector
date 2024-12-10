<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Mirakl\Mcm\Helper\Data as McmDataHelper;
use Mirakl\Mcm\Model\Product\Import\Adapter\Bulk;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter;
use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;

/**
 * @group MCM
 * @group CM51
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkImportProductTest extends MiraklBaseTestCase
{
    /**
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     */
    public function testMcmBulkAdapter()
    {
        /** @var \Mirakl\Mcm\Model\Product\Import\Adapter\AdapterFactory $adapterFactory */
        $adapterFactory = $this->objectManager->get(\Mirakl\Mcm\Model\Product\Import\Adapter\AdapterFactory::class);
        $this->assertInstanceOf(Bulk::class, $adapterFactory->create());
    }

    /**
     * @dataProvider importMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $csv
     * @param array  $miraklProductIds
     */
    public function testDataMcmImport(string $csv, array $miraklProductIds)
    {
        $process = $this->runImport($csv);

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste',
            'name'               => 'Slim Fit Polo',
            'description'        => 'This ...',
            'color'              => '50',
            'size'               => '91',
            'material'           => '33,146',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_DISABLED,
        ];

        $this->validateAllProductValues($miraklProductIds, $values);

        /** @var \Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\Mcm $adapter */
        $adapter = $this->objectManager->get(EntityAdapter\Mcm::class);
        $this->assertCount(0, $adapter->getOldSku());

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());
    }

    /**
     * @dataProvider importMcmDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 1
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     *
     * @param string $csv
     * @param array  $miraklProductIds
     */
    public function testEnableProductMcmImport(string $csv, array $miraklProductIds)
    {
        $this->runImport($csv);

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
    public function importMcmDataProvider(): array
    {
        return [
            ['CM51_single_product2.csv', ['abc5-4cf1-acdb-56152a77bc65']],
        ];
    }
}
