<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreFactory $storeFactory */
$storeFactory = $objectManager->get(\Magento\Store\Model\StoreFactory::class);

$storeFR = $storeFactory->create();

// @phpstan-ignore-next-line
$storeFR->setCode('fr')
    ->setWebsiteId(1)
    ->setGroupId(1)
    ->setName('French')
    ->setSortOrder(2)
    ->setIsActive(1)
    ->save();
