<?php
declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\InventorySales\OrderManagement;

use Magento\InventorySales\Plugin\Sales\OrderManagement\AppendReservationsAfterOrderPlacementPlugin as Plugin;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class AppendReservationsAfterOrderPlacementPlugin
{
    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var SalesEventExtensionFactory;
     */
    private $salesEventExtensionFactory;

    /**
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param SalesEventExtensionFactory $salesEventExtensionFactory
     */
    public function __construct(
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        SalesEventExtensionFactory $salesEventExtensionFactory
    ) {
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->salesEventExtensionFactory = $salesEventExtensionFactory;
    }

    /**
     * This is a plugin on a plugin implemented since Magento 2.3.0.
     * The default plugin handles stock reservations after an order is placed
     * but we have to exclude marketplace order items from it.
     *
     * @see AppendReservationsAfterOrderPlacementPlugin::afterPlace
     *
     * @param   Plugin                      $plugin
     * @param   \Closure                    $proceed
     * @param   OrderManagementInterface    $orderManagement
     * @param   OrderInterface              $order
     * @return  OrderInterface
     */
    public function aroundAfterPlace(
        Plugin $plugin,
        \Closure $proceed,
        OrderManagementInterface $orderManagement,
        OrderInterface $order
    ): OrderInterface {
        $items = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            $parentItem = $item->getParentItem();
            if (!$item->getMiraklOfferId() && (!$parentItem || !$parentItem->getMiraklOfferId())) {
                $items[] = $item; // Handle item only if it's not a marketplace item
            }
        }

        if (empty($items)) {
            return $order; // Do not call the default plugin if no operator item is present
        }

        $modifiedOrder = clone $order; // Do not interfere with the default order object
        $modifiedOrder->setItems($items);

        return $proceed($orderManagement, $modifiedOrder);
    }

    /**
     * This is a plugin on a plugin implemented since Magento 2.4.0.
     * The default plugin handles stock reservations after an order is placed
     * but we have to add compensation for marketplace order items that are not part
     * of the default Magento stock management.
     *
     * @see AppendReservationsAfterOrderPlacementPlugin::aroundPlace
     *
     * @param Plugin $plugin
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterAroundPlace(Plugin $plugin, OrderInterface $order): OrderInterface
    {
        $itemsBySku = $itemsToCancel = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getItems() as $item) {
            if ($item->getMiraklOfferId()) {
                if (!isset($itemsBySku[$item->getSku()])) {
                    $itemsBySku[$item->getSku()] = 0;
                }
                $itemsBySku[$item->getSku()] += $item->getQtyOrdered();
            }
        }

        if (empty($itemsBySku)) {
            return $order;
        }

        /** @var SalesEventExtensionInterface $salesEventExtension */
        $salesEventExtension = $this->salesEventExtensionFactory->create([
            'data' => ['objectIncrementId' => (string) $order->getIncrementId()]
        ]);

        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type'       => 'mirakl_order_placed_compensation',
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId'   => (string) $order->getEntityId()
        ]);

        $salesEvent->setExtensionAttributes($salesEventExtension);
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $order->getStore()->getWebsite()->getCode()
            ]
        ]);

        foreach ($itemsBySku as $sku => $qtyOrdered) {
            $itemsToCancel[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => (float) $qtyOrdered
            ]);
        }

        $this->placeReservationsForSalesEvent->execute($itemsToCancel, $salesChannel, $salesEvent);

        return $order;
    }
}