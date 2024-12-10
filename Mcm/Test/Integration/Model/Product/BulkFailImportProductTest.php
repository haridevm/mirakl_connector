<?php
namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation disabled
 */
class BulkFailImportProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importMcmErrorDataProvider
     *
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product/mode bulk
     *
     * @param   string  $csv
     * @param   array   $errors
     */
    public function testDataErrorMcmImport($csv, $errors)
    {
        $this->runImport($csv);

        foreach ($errors as $error) {
            $this->assertStringContainsString($error, $this->processModel->getOutput());
        }
    }

    /**
     * @return  array
     */
    public function importMcmErrorDataProvider()
    {
        return [
            ['CM51_single_product_category_not_found.csv', ['Could not find category with id "POLO"']],
            ['CM51_single_product_attribute_set_not_found.csv', ['Could not find attribute set for category "3"']],
            ['CM51_empty.csv', ['Importing MCM file...']],
            ['CM51_without_mirakl_product_id.csv', ['Column "mirakl-product-id" cannot be empty']],
            ['CM51_without_mirakl_product_id_value.csv', ['Column "mirakl-product-id" cannot be empty']],
            ['CM51_without_category.csv', ['Could not find "category" field in product data']],
        ];
    }
}