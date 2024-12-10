<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Inventory\Store;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Connector\Helper\Stock as StockHelper;

class StockIdResolver
{
    /**
     * Need to use object manager if MSI is disabled
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockHelper
     */
    private $stockHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param StockHelper $stockHelper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StockHelper $stockHelper
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param int $storeId
     * @return int
     * @throws NoSuchEntityException
     */
    public function resolve(int $storeId): int
    {
        if (!$this->stockHelper->isMultiInventoryEnabled()) {
            return Stock::DEFAULT_STOCK_ID;
        }

        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);
        $website = $store->getWebsite();
        $stockId = $this->objectManager->get('Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite')
            ->execute($website->getCode());

        if (null === $stockId) {
            /** @var \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver */
            $stockResolver = $this->objectManager->get('Magento\InventorySalesApi\Api\StockResolverInterface');
            $stock = $stockResolver->execute('website', $website->getCode());
            $stockId = (int) $stock->getStockId();
        }

        return $stockId;
    }
}
