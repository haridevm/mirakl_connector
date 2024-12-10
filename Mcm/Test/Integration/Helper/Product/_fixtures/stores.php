<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreFactory $storeFactory */
$storeFactory = $objectManager->get(\Magento\Store\Model\StoreFactory::class);

// Create 2 stores with the same locale but the Belgium store with higher priority
$storeFR = $storeFactory->create();
// @phpstan-ignore-next-line
$storeFR->setCode('fr')
    ->setWebsiteId(1)
    ->setGroupId(1)
    ->setName('France')
    ->setSortOrder(2)
    ->setIsActive(1)
    ->save();

$storeBE = $storeFactory->create();
// @phpstan-ignore-next-line
$storeBE->setCode('be')
    ->setWebsiteId(1)
    ->setGroupId(1)
    ->setName('Belgium')
    ->setSortOrder(1)
    ->setIsActive(1)
    ->save();
