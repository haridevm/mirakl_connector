<?php
namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Mirakl\Mcm\Test\Integration\Model\Product\AbstractImportMcmProductTestCase as MiraklBaseTestCase;

/**
 * @group MCM
 * @group CM54
 * @group bulk
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AsyncBulkFailImportProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importAsyncMcmErrorDataProvider
     *
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     *
     * @param   string  $jsonFileName
     * @param   array   $errors
     */
    public function testDataErrorMcmImport($jsonFileName, $errors)
    {
        $this->runAsyncImport($jsonFileName);
        foreach ($errors as $error) {
            $this->assertStringContainsString($error, $this->processModel->getOutput());
        }
    }

    /**
     * @return  array
     */
    public function importAsyncMcmErrorDataProvider()
    {
        return [
            ['CM54_single_product_category_not_found.json', ['Could not find category with id "POLO"']],
            ['CM54_single_product_attribute_set_not_found.json', ['Could not find attribute set for category "3"']],
            ['CM54_empty.json', ['Importing MCM file...']],
            ['CM54_without_mirakl_product_id.json', ['"mirakl_product_id" cannot be empty']],
            ['CM54_without_mirakl_product_id_value.json', ['"mirakl_product_id" cannot be empty']],
            ['CM54_without_category.json', ['Could not find "category" field in product data']],
        ];
    }
}