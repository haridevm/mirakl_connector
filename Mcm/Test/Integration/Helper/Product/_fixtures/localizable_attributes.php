<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\AttributeRepository $attributeRepository */
$attributeRepository = $objectManager->get(\Magento\Eav\Model\AttributeRepository::class);

foreach (['name', 'description'] as $attrCode) {
    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
    $attribute = $attributeRepository->get('catalog_product', $attrCode);
    $attribute->setMiraklIsLocalizable(1);
    $attribute->save(); // @phpstan-ignore-line
}
