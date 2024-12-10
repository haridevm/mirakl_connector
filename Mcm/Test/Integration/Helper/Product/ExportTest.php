<?php
namespace Mirakl\Mcm\Test\Integration\Helper\Product;

use Mirakl\Mcm\Helper\Product\Export\Product as ProductHelper;

/**
 * @group MCM
 * @group CM21
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ExportTest extends \Mirakl\Core\Test\Integration\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productHelper = $this->objectManager->get(ProductHelper::class);
    }

    /**
     * @group CM21
     *
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 1
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 0
     * @magentoConfigFixture fr_store general/locale/code fr_FR
     * @magentoConfigFixture be_store general/locale/code fr_FR
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/stores.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/localizable_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/products.php
     */
    public function testGetProductsDataForSyncMcm()
    {
        $data = $this->productHelper->getProductsData([1, 25]);

        $this->assertSame($data['1']['name'], $data['1']['name-en_US']);
        $this->assertSame('Joust Duffle Bag [BE]', $data['1']['name-fr_FR']);

        $this->assertSame($data['25']['description'], $data['25']['description-en_US']);
        $this->assertSame('Sprite Stasis Ball 55 cm [BE]', $data['25']['description-fr_FR']);
    }

    /**
     * @group CM21
     *
     * @magentoConfigFixture current_store mirakl_mcm/import_product/enable_mcm 0
     * @magentoConfigFixture current_store mirakl_mcm/import_product_async/enable_mcm 1
     * @magentoConfigFixture fr_store general/locale/code fr_FR
     * @magentoConfigFixture be_store general/locale/code fr_FR
     *
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/stores.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/localizable_attributes.php
     * @magentoDataFixture Mirakl_Mcm::Test/Integration/Helper/Product/_fixtures/products.php
     */
    public function testGetProductsDataForAsyncMcm()
    {
        $data = $this->productHelper->getProductsData([1, 25]);

        $this->assertSame($data['1']['name'], $data['1']['name-en_US']);
        $this->assertSame('Joust Duffle Bag [BE]', $data['1']['name-fr_FR']);

        $this->assertSame($data['25']['description'], $data['25']['description-en_US']);
        $this->assertSame('Sprite Stasis Ball 55 cm [BE]', $data['25']['description-fr_FR']);
    }
}
