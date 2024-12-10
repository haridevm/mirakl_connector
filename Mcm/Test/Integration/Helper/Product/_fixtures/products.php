<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreRepository $storeRepository */
$storeRepository = $objectManager->get(\Magento\Store\Model\StoreRepository::class);

$storeFR = $storeRepository->get('fr');
$storeBE = $storeRepository->get('be');

/** @var \Magento\Catalog\Model\ResourceModel\Product\Action $productAction */
$productAction = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\Action::class);

$productAction->updateAttributes([1], ['name' => 'Joust Duffle Bag [FR]'], $storeFR->getId());
$productAction->updateAttributes([1], ['name' => 'Joust Duffle Bag [BE]'], $storeBE->getId());

$productAction->updateAttributes([25], ['description' => 'Sprite Stasis Ball 55 cm [FR]'], $storeFR->getId());
$productAction->updateAttributes([25], ['description' => 'Sprite Stasis Ball 55 cm [BE]'], $storeBE->getId());
