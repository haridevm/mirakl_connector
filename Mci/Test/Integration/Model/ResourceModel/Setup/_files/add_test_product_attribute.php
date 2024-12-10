<?php
/**
 * Data fixture to set up a test attribute and confirm you can set its
 * Mirakl settings to the inverse of the default
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Helper\Bootstrap;

$attributeCode = 'test_mirakl_attribute';

$attributeData = [
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Test Mirakl Attribute',
    'input' => 'select',
    'class' => '',
    'source' => '',
    'global' => 1,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => null,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'used_in_product_listing' => false,
    'mirakl_is_variant' => true,
    'mirakl_is_exportable' => false,
    'mirakl_is_localizable' => true,
];

$objectManager = Bootstrap::getObjectManager();

$setup = $objectManager->get(ModuleDataSetupInterface::class);
/** @var EavSetup $eavSetup */
$eavSetup = $objectManager->get(EavSetupFactory::class)
    ->create(['setup' => $setup]);

$eavSetup->addAttribute(
    Product::ENTITY,
    $attributeCode,
    $attributeData
);
