<?php
declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\InventorySales\IsProductSalableCondition;

use Magento\InventorySales\Model\IsProductSalableCondition\IsAnySourceItemInStockCondition;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

class IsAnySourceItemInStockConditionPlugin
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(GetStockItemDataInterface $getStockItemData)
    {
        $this->getStockItemData = $getStockItemData;
    }

    /**
     * @param IsAnySourceItemInStockCondition $subject
     * @param bool $result
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function afterExecute(
        IsAnySourceItemInStockCondition $subject,
        bool $result,
        string $sku,
        int $stockId
    ): bool {
        if (!$result) {
            // If all source items are "out of stock", maybe the product has been
            // set as "in stock" because some offers are available for this sku.
            // So we verify the stock status directly in the index table.
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            $result = !empty($stockItemData[GetStockItemDataInterface::IS_SALABLE]);
        }

        return $result;
    }
}
