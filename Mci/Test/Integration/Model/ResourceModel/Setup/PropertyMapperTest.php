<?php
declare(strict_types=1);

namespace Mirakl\Mci\Test\Integration\Model\ResourceModel\Setup;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Mirakl\Core\Test\Integration\TestCase;

/**
 * @covers \Mirakl\Mci\Model\ResourceModel\Setup\PropertyMapper
 */
class PropertyMapperTest extends TestCase
{
    /**
     * Test adding an attribute and Mirakl settings to non default values
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/ResourceModel/Setup/_files/add_test_product_attribute.php
     * @return void
     */
    public function testAddAttribute(): void
    {
        /** @var ProductAttributeRepositoryInterface $productAttributeRepository */
        $productAttributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);

        $attribute = $productAttributeRepository->get('test_mirakl_attribute');

        $this->assertEquals(0, $attribute->getData('mirakl_is_exportable'));
        $this->assertEquals(1, $attribute->getData('mirakl_is_variant'));
        $this->assertEquals(1, $attribute->getData('mirakl_is_localizable'));
    }
}
