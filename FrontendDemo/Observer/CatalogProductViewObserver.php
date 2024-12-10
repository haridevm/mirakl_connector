<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\Connector\Model\Product\Inventory\IsOperatorProductAvailable;
use Mirakl\Connector\Model\Product\Offer\CollectorInterface as OfferCollectorInterface;

class CatalogProductViewObserver implements ObserverInterface
{
    /**
     * @var IsOperatorProductAvailable
     */
    private $isOperatorProductAvailable;

    /**
     * @var OfferCollectorInterface
     */
    private $productOfferCollector;

    /**
     * @var Configuration
     */
    private $inventoryConfig;

    /**
     * @param IsOperatorProductAvailable $isOperatorProductAvailable
     * @param OfferCollectorInterface    $productOfferCollector
     * @param Configuration              $inventoryConfig
     */
    public function __construct(
        IsOperatorProductAvailable $isOperatorProductAvailable,
        OfferCollectorInterface $productOfferCollector,
        Configuration $inventoryConfig
    ) {
        $this->isOperatorProductAvailable = $isOperatorProductAvailable;
        $this->productOfferCollector = $productOfferCollector;
        $this->inventoryConfig = $inventoryConfig;
    }

    /**
     * @inheritdoc
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        $showOutOfStockProducts = $this->inventoryConfig->isShowOutOfStock();
        if ($showOutOfStockProducts || $this->isOperatorProductAvailable->execute($product)) {
            return;
        }

        if (!$this->productOfferCollector->collect($product)->count()) {
            throw new NoSuchEntityException(__('Product has no available offers'));
        }
    }
}
