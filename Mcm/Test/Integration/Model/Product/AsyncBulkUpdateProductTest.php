<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
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
class AsyncBulkUpdateProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider asyncImportUpdateMcmDataProvider
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
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
     * @param string $insertJsonFileName
     * @param string $updateJsonFileName
     * @param string $miraklProductId
     */
    public function testUpdateProductMcmAsyncImport(
        string $insertJsonFileName,
        string $updateJsonFileName,
        string $miraklProductId
    ) {
        $process = $this->runAsyncImport($insertJsonFileName);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        $this->runAsyncImport($updateJsonFileName);

        $this->assertStringContainsString('Bulk import time', $process->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $process->getOutput());

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste Update',
            'name'               => 'Slim Fit Polo UPDATE',
            'description'        => 'This ...UPDATE',
            'color'              => '54',
            'size'               => '167',
            'material'           => '33,146',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_DISABLED,
        ];

        $this->validateAllProductValues([$miraklProductId], $values);

        /** @var \Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\Mcm $adapter */
        $adapter = $this->objectManager->get(EntityAdapter\Mcm::class);
        $this->assertCount(1, $adapter->getOldSku());
    }

    /**
     * @return array
     */
    public function asyncImportUpdateMcmDataProvider(): array
    {
        return [
            ['CM54_single_product.json', 'CM54_single_product_update.json', 'abc5-4cf1-acdb-56152a77bc56'],
        ];
    }
}
