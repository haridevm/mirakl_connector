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
 * @group CM51
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkImportProductDeduplicationTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importDeduplicationMcmDataProvider
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/single_product.php
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @param string $updateCsv
     * @param string $miraklProductId
     */
    public function testDeduplicationProductMcmImport(string $updateCsv, string $miraklProductId)
    {
        $process = $this->runImport($updateCsv);

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

        // Check that visibility and tax class id did not change after update.
        $this->assertEquals(0, $newProduct->getTaxClassId());

        // Variant product
        $this->assertEquals(Product\Visibility::VISIBILITY_NOT_VISIBLE, $newProduct->getVisibility());

        $this->validateAllProductValues([$miraklProductId], $values);

        /** @var \Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\Mcm $adapter */
        $adapter = $this->objectManager->get(EntityAdapter\Mcm::class);
        $this->assertCount(1, $adapter->getOldSku());
    }

    /**
     * @return array
     */
    public function importDeduplicationMcmDataProvider(): array
    {
        return [
            ['CM51_single_product_update.csv', 'abc5-4cf1-acdb-56152a77bc56'],
        ];
    }
}
