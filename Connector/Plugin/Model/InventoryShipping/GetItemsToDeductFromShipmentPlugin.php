<?php
namespace Mirakl\Connector\Plugin\Model\InventoryShipping;

use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface;
use Magento\Sales\Model\Order\Shipment;

class GetItemsToDeductFromShipmentPlugin
{
    /**
     * @param   GetItemsToDeductFromShipment    $subject
     * @param   array                           $result
     * @param   Shipment                        $shipment
     * @return  ItemToDeductInterface[]
     */
    public function afterExecute(GetItemsToDeductFromShipment $subject, array $result, Shipment $shipment)
    {
        /** @var Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var ItemToDeductInterface $itemToDeduct */
            foreach ($result as $key => $itemToDeduct) {
                $orderItem = $shipmentItem->getOrderItem();
                if ($itemToDeduct->getSku() === $orderItem->getSku() && $orderItem->getMiraklOfferId()) {
                    // Remove Mirakl items from being used for inventory deduction when creating shipment
                    unset($result[$key]);
                    continue 2;
                }
            }
        }

        return $result;
    }
}
