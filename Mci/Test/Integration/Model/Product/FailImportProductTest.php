<?php

declare(strict_types=1);

namespace Mirakl\Mci\Test\Integration\Model\Product;

use Mirakl\Mci\Test\Integration\Model\Product\AbstractImportProductTestCase as MiraklBaseTestCase;

/**
 * @group MCI
 * @group import
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class FailImportProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importMciAttributeSetErrorDataProvider
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set_rollback.php
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     *
     * @param string $csv
     * @param array  $errors
     */
    public function testDataAttributeSetErrorMciImport($csv, $errors)
    {
        $process = $this->runImport('2010', $csv);

        foreach ($errors as $error) {
            $this->assertStringContainsString($error, $process->getOutput());
        }
    }

    /**
     * @dataProvider importMciErrorDataProvider
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     *
     * @param string $csv
     * @param array  $errors
     */
    public function testDataErrorMciImport($csv, $errors)
    {
        try {
            $process = $this->runImport('2010', $csv);

            $this->assertGreaterThan(0, count($errors));

            foreach ($errors as $error) {
                $this->assertStringContainsString($error, $process->getOutput());
            }
        } catch (\Exception $e) {
            // Do not stop test on exception
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function importMciErrorDataProvider()
    {
        return [
            ['single_product_with_category_not_found.csv', ['Could not find category with id "1024"']],
            ['single_product_without_shop_sku.csv', ['Could not find "shop_sku" column in product data']],
            ['single_product_with_empty_shop_sku.csv', ['Column "shop_sku" cannot be empty']],
            ['empty.csv', ['No valid delimiter found.']],
            ['single_product_without_category.csv', ['Undefined array key "category"']],
        ];
    }

    /**
     * @return array
     */
    public function importMciAttributeSetErrorDataProvider()
    {
        return [
            ['single_product_invalid_shop_sku.csv', ['Could not find attribute set for category "3"']],
        ];
    }
}
