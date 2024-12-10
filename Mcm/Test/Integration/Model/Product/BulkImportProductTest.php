<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Mirakl\Mcm\Helper\Data as McmDataHelper;
use Mirakl\Mcm\Model\Product\Import\Adapter\Bulk;
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
     * @param   string  $csv
     * @param   array   $miraklProductIds
     */
    public function testDataMcmImport(string $csv, array $miraklProductIds)
    {
        $this->runImport($csv);

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste',
            'name'               => 'Slim Fit Polo',
            'description'        => 'This ...',
            'color'              => '50',
            'size'               => '91',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_DISABLED,
        ];

        $this->validateAllProductValues($miraklProductIds, $values);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());
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
     * @param   string  $csv
     * @param   array   $miraklProductIds
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
     * @return  array
     */
    public function importMcmDataProvider(): array
    {
        return [
            ['CM51_single_product2.csv', ['abc5-4cf1-acdb-56152a77bc65']],
        ];
    }

    /**
     * @dataProvider importUpdateMcmDataProvider
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_sync/mcm_products/enable_mcm_products 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     * @magentoConfigFixture current_store mirakl_mcm/import_product/auto_enable_product 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mcm/import_product/default_visibility 4
     *
     * @param   string  $insertCsv
     * @param   string  $updateCsv
     * @param   string  $miraklProductId
     */
    public function testUpdateProductMcmImport(string $insertCsv, string $updateCsv, string $miraklProductId)
    {
        $this->runImport($insertCsv);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());

        $this->runImport($updateCsv);

        $this->assertStringContainsString('Bulk import time', $this->processModel->getOutput());
        $this->assertStringContainsString('invalid rows: 0', $this->processModel->getOutput());

        $values = [
            'mirakl_category_id' => 3,
            'brand'              => 'Lacoste Update',
            'name'               => 'Slim Fit Polo UPDATE',
            'description'        => 'This ...UPDATE',
            'color'              => '54',
            'size'               => '167',
            'mirakl_image_1'     => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'             => Status::STATUS_DISABLED,
        ];

        $this->validateAllProductValues([$miraklProductId], $values);
    }

    /**
     * @return  array
     */
    public function importUpdateMcmDataProvider(): array
    {
        return [
            ['CM51_single_product.csv', 'CM51_single_product_update.csv', 'abc5-4cf1-acdb-56152a77bc56'],
        ];
    }
}
