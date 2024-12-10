<?php

declare(strict_types=1);

namespace Mirakl\Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\OrderFactory as OrderResourceFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Mirakl\Api\Helper\Order as OrderApiHelper;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Connector\Model\Order\Converter as OrderConverter;
use Mirakl\Core\Helper\Config as CoreConfig;
use Mirakl\MMP\Common\Domain\Order\OrderState;
use Mirakl\MMP\Common\Domain\Order\Refund;
use Mirakl\MMP\Common\Domain\Order\Refund\RefundState;
use Mirakl\MMP\Common\Domain\Payment\PaymentWorkflow;
use Mirakl\MMP\Common\Domain\Reason\ReasonType;
use Mirakl\MMP\Front\Domain\Order\Create\CreatedOrders;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;

/**
 * @SuppressWarnings(PHPMD)
 */
class Order extends AbstractHelper
{
    /**
     * @var CoreConfig
     */
    protected $coreConfig;

    /**
     * @var ConnectorConfig
     */
    protected $connectorConfig;

    /**
     * @var OrderApiHelper
     */
    protected $orderApiHelper;

    /**
     * @var OrderConverter
     */
    protected $orderConverter;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var OrderResourceFactory
     */
    protected $orderResourceFactory;

    /**
     * @var Tax
     */
    protected $taxHelper;

    /**
     * @param Context              $context
     * @param CoreConfig           $coreConfig
     * @param Config               $connectorConfig
     * @param OrderApiHelper       $orderApiHelper
     * @param OrderConverter       $orderConverter
     * @param OrderResourceFactory $orderResourceFactory
     * @param Tax                  $taxHelper
     * @param State                $appState
     */
    public function __construct(
        Context $context,
        CoreConfig $coreConfig,
        ConnectorConfig $connectorConfig,
        OrderApiHelper $orderApiHelper,
        OrderConverter $orderConverter,
        OrderResourceFactory $orderResourceFactory,
        Tax $taxHelper,
        State $appState
    ) {
        parent::__construct($context);
        $this->coreConfig           = $coreConfig;
        $this->orderApiHelper       = $orderApiHelper;
        $this->orderConverter       = $orderConverter;
        $this->orderResourceFactory = $orderResourceFactory;
        $this->appState             = $appState;
        $this->connectorConfig      = $connectorConfig;
        $this->taxHelper            = $taxHelper;
    }

    /**
     * Adds Mirakl orders information to specified Magento orders
     *
     * @param OrderCollection $collection
     * @return $this
     */
    public function addMiraklOrdersToCollection(OrderCollection $collection)
    {
        $commercialIds = [];
        foreach ($collection as $order) {
            /** @var OrderModel $order */
            if ($order->getMiraklSent()) {
                $commercialIds[] = $order->getIncrementId();
            }
        }

        if (empty($commercialIds)) {
            return $this;
        }

        $miraklOrders = $this->orderApiHelper->getOrdersByCommercialId($commercialIds);
        foreach ($collection as $order) {
            $addOrders = [];
            foreach ($miraklOrders as $miraklOrder) {
                /** @var MiraklOrder $miraklOrder */
                if ($miraklOrder->getCommercialId() == $order->getIncrementId()) {
                    $addOrders[] = $miraklOrder;
                }
            }
            $order->setMiraklOrders($addOrders);
        }

        return $this;
    }

    /**
     * @param OrderModel $order
     * @return OrderModel
     * @throws \Exception
     */
    public function autoCreateMiraklOrder(OrderModel $order)
    {
        if (!$this->connectorConfig->isAutoCreateOrder()) {
            return $order;
        }

        $validStatus = in_array($order->getStatus(), $this->connectorConfig->getCreateOrderStatuses());
        $alreadySent = $order->getData('mirakl_sent');

        if ($validStatus && !$alreadySent && $this->isMiraklOrder($order)) {
            $this->createMiraklOrder($order);
        }

        return $order;
    }

    /**
     * Creates Mirakl order and set Magento order as sent if creation succeeded
     *
     * @param OrderModel $order
     * @param bool       $markAsSent
     * @return CreatedOrders
     * @throws \Exception
     */
    public function createMiraklOrder(OrderModel $order, $markAsSent = true)
    {
        $createdOrders = $this->orderApiHelper->createOrder($this->orderConverter->convert($order));

        if ($markAsSent && $createdOrders->getOrders()->count()) {
            $order->setMiraklSent(1);
            $this->orderResourceFactory->create()->saveAttribute($order, 'mirakl_sent');
        }

        return $createdOrders;
    }

    /**
     * Returns shipping price in base currency of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklBaseShippingPriceInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['mirakl_base_shipping_incl_tax']);
    }

    /**
     * Returns shipping price of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklShippingPriceInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['mirakl_shipping_incl_tax']);
    }

    /**
     * Returns shipping price excluding tax of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklShippingPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['mirakl_shipping_excl_tax']);
    }

    /**
     * Returns subtotal price of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklSubtotalPrice(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['row_total_incl_tax']);
    }

    /**
     * Returns subtotal price excluding tax of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklSubtotalPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['row_total']);
    }

    /**
     * Returns base total price of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklBaseTotalPrice(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, [
            'base_row_total_incl_tax',
            'mirakl_base_shipping_incl_tax'
        ]);
    }

    /**
     * Returns total price of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklTotalPrice(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, ['row_total_incl_tax', 'mirakl_shipping_incl_tax']);
    }

    /**
     * Returns final total amount paid by customer in case of cancelations/rejections and refunds
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklPaidTotal(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $finalTotalPrice = $this->getOrderFinalTotalPriceInclTax($order, $miraklOrder);
        $refundTotal = $this->getMiraklRefundTotal($order, $miraklOrder);

        return (float) ($finalTotalPrice - $refundTotal);
    }

    /**
     * Checks if order has Mirakl refunds
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return bool
     */
    public function hasMiraklRefunds(OrderModel $order, MiraklOrder $miraklOrder)
    {
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        foreach ($orderLine->getRefunds() as $refund) {
                            if ($refund->getState() === RefundState::REFUNDED) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns Mirakl refund total
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklRefundTotal(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $refundTotal = 0;
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        /** @var Refund $refund */
                        foreach ($orderLine->getRefunds() as $refund) {
                            if ($refund->getState() === RefundState::REFUNDED) {
                                $refundTotal += (float) ($refund->getAmount() + $refund->getShippingAmount());
                                foreach ($refund->getTaxes() as $tax) {
                                    $refundTotal += (float) $tax->getAmount();
                                }
                                foreach ($refund->getShippingTaxes() as $tax) {
                                    $refundTotal += (float) $tax->getAmount();
                                }
                            }
                        }
                    }
                }
            }
        }

        return (float) $refundTotal;
    }

    /**
     * Check if order has rejected Mirakl items by seller
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return bool
     */
    public function hasRejectedMiraklItems(OrderModel $order, MiraklOrder $miraklOrder)
    {
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if (
                        $orderLine->getOffer()
                        && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()
                        && $this->isOrderLineRefused($orderLine)
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if order has canceled Mirakl items by seller
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return bool
     */
    public function hasCancelations(OrderModel $order, MiraklOrder $miraklOrder)
    {
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        if ($this->isOrderLineCanceled($orderLine)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns Mirakl canceled/refused items total including tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklCanceledAndRefusedItemsTotalInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $canceledTotalInclTax = 0;
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        if ($this->isOrderLineRefused($orderLine)) {
                            $canceledTotalInclTax += (float) $orderLine->getPrice();
                            foreach ($orderLine->getTaxes() as $tax) {
                                $canceledTotalInclTax += (float) $tax->getAmount();
                            }
                        } elseif ($this->isOrderLineCanceled($orderLine)) {
                            foreach ($orderLine->getCancelations() as $cancelation) {
                                $canceledTotalInclTax += (float) $cancelation->getAmount();
                                foreach ($cancelation->getTaxes() as $tax) {
                                    $canceledTotalInclTax += (float) $tax->getAmount();
                                }
                            }
                        }
                    }
                }
            }
        }

        return (float) $canceledTotalInclTax;
    }

    /**
     * Returns Mirakl canceled/refused items total excluding tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklCanceledItemsTotalExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $canceledTotalExclTax = 0;
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        if ($this->isOrderLineRefused($orderLine)) {
                            $canceledTotalExclTax += (float) $orderLine->getPrice();
                        } elseif ($this->isOrderLineCanceled($orderLine)) {
                            foreach ($orderLine->getCancelations() as $cancellation) {
                                $canceledTotalExclTax += (float) $cancellation->getAmount();
                            }
                        }
                    }
                }
            }
        }

        return (float) $canceledTotalExclTax;
    }

    /**
     * Returns Mirakl final subtotal (after cancelations/rejetctions) including tax
     *
     * @param OrderLine   $orderLine
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalSubtotalInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $subtotalInclTax = $this->getMiraklSubtotalPrice($order, $miraklOrder);
        $canceledItemsTotalInclTax = $this->getMiraklCanceledAndRefusedItemsTotalInclTax($order, $miraklOrder);

        return (float) ($subtotalInclTax - $canceledItemsTotalInclTax);
    }

    /**
     * Returns Mirakl final subtotal (after cancelations/rejections) excluding tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalSubtotalExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $subtotalExclTax = $this->getMiraklSubtotalPriceExclTax($order, $miraklOrder);
        $canceledItemsTotalExclTax = $this->getMiraklCanceledItemsTotalExclTax($order, $miraklOrder);

        return (float) ($subtotalExclTax - $canceledItemsTotalExclTax);
    }

    /**
     * Returns Mirakl canceled/refused items shipping price including tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderCanceledAndRefusedShippingPriceInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $canceledShippingPriceInclTax = 0;
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        if ($this->isOrderLineRefused($orderLine)) {
                            $canceledShippingPriceInclTax += (float) $orderLine->getShippingPrice();
                            foreach ($orderLine->getShippingTaxes() as $tax) {
                                $canceledShippingPriceInclTax += (float) $tax->getAmount();
                            }
                        } elseif ($this->isOrderLineCanceled($orderLine)) {
                            foreach ($orderLine->getCancelations() as $cancelation) {
                                $canceledShippingPriceInclTax += (float) $cancelation->getShippingAmount();
                                foreach ($cancelation->getShippingTaxes() as $tax) {
                                    $canceledShippingPriceInclTax += (float) $tax->getAmount();
                                }
                            }
                        }
                    }
                }
            }
        }

        return (float) $canceledShippingPriceInclTax;
    }

    /**
     * Returns Mirakl canceled/refused items shipping price excluding tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderCanceledAndRefusedShippingPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $canceledShippingPriceExclTax = 0;
        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        if ($this->isOrderLineRefused($orderLine)) {
                            $canceledShippingPriceExclTax += (float) $orderLine->getShippingPrice();
                        } elseif ($this->isOrderLineCanceled($orderLine)) {
                            foreach ($orderLine->getCancelations() as $cancelation) {
                                $canceledShippingPriceExclTax += (float) $cancelation->getShippingAmount();
                            }
                        }
                    }
                }
            }
        }

        return (float) $canceledShippingPriceExclTax;
    }

    /**
     * Returns Mirakl final shipping price (after cancelations/rejections) including tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalShippingPriceInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $orderShippingPriceInclTax = $this->getMiraklShippingPriceInclTax($order, $miraklOrder);
        $canceledShippingPriceInclTax = $this->getOrderCanceledAndRefusedShippingPriceInclTax($order, $miraklOrder);
        $shippingPriceAfterCancelationInclTax = (float) ($orderShippingPriceInclTax - $canceledShippingPriceInclTax);

        return $shippingPriceAfterCancelationInclTax > 0 ? $shippingPriceAfterCancelationInclTax : 0;
    }

    /**
     * Returns Mirakl final shipping price (after cancelations/rejections) excluding tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalShippingPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $orderShippingPriceExclTax = $this->getMiraklShippingPriceExclTax($order, $miraklOrder);
        $canceledShippingPriceExclTax = $this->getOrderCanceledAndRefusedShippingPriceExclTax($order, $miraklOrder);
        $shippingPriceAfterCancelationExclTax = (float) ($orderShippingPriceExclTax - $canceledShippingPriceExclTax);

        return $shippingPriceAfterCancelationExclTax > 0 ? $shippingPriceAfterCancelationExclTax : 0;
    }

    /**
     * Returns order line price including tax after cancelations
     *
     * @param OrderLine $orderLine
     * @return float
     */
    public function getOrderLinePriceAfterCancelationInclTax(OrderLine $orderLine)
    {
        $orderLinePriceAfterCancelationInclTax = (float) $orderLine->getPrice();
        foreach ($orderLine->getTaxes() as $tax) {
            $orderLinePriceAfterCancelationInclTax += (float) $tax->getAmount();
        }

        return (float) $orderLinePriceAfterCancelationInclTax;
    }

    /**
     * Returns Mirakl order final total price (after cancelations, rejections) excluding tax
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalTotalPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $orderFinalSubtotalExclTax = $this->getOrderFinalSubtotalExclTax($order, $miraklOrder);
        $orderFinalShippingExclTax = $this->getOrderFinalShippingPriceExclTax($order, $miraklOrder);

        return (float) ($orderFinalSubtotalExclTax + $orderFinalShippingExclTax);
    }

    /**
     * Returns Mirakl order final total price (after cancelations, rejections) including tax
     *
     * @param OrderLine   $orderLine
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getOrderFinalTotalPriceInclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $orderFinalSubtotalInclTax = $this->getOrderFinalSubtotalInclTax($order, $miraklOrder);
        $orderFinalShippingInclTax = $this->getOrderFinalShippingPriceInclTax($order, $miraklOrder);

        return (float) ($orderFinalSubtotalInclTax + $orderFinalShippingInclTax);
    }

    /**
     * Checks if order line is cancelled (full or partial cancellation)
     *
     * @param OrderLine $orderLine
     * @return bool
     */
    public function isOrderLineCanceled(OrderLine $orderLine)
    {
        return $orderLine->getCancelations()->count() > 0;
    }

    /**
     * Returns cancelled qty of an order line
     *
     * @param OrderLine $orderLine
     * @return int
     */
    public function getCanceledOrderLineQuantity(OrderLine $orderLine)
    {
        $canceledQty = 0;
        foreach ($orderLine->getCancelations() as $cancelation) {
            $canceledQty += $cancelation->getQuantity();
        }

        return (int) $canceledQty;
    }

    /**
     * Returns total quantity of an order line
     *
     * @param OrderLine $orderLine
     * @return int
     */
    public function getOrderLineTotalQuantity(OrderLine $orderLine)
    {
        return (int) ($orderLine->getQuantity() + $this->getCanceledOrderLineQuantity($orderLine));
    }

    /**
     * Returns total tax amount of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklTotalTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklTotal($order, $miraklOrder, [
            'tax_amount',
            'mirakl_shipping_tax_amount',
            'mirakl_custom_shipping_tax_amount',
        ]);
    }

    /**
     * Returns final total tax of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklFinalTotalTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        $taxTotal = (float) $this->getMiraklTotalTax($order, $miraklOrder);
        $canceledAndRefusedTaxes = $this->taxHelper->getMiraklCancelationAndRejectionTaxes($order, $miraklOrder);
        foreach ($canceledAndRefusedTaxes as $taxCollection) {
            foreach ($taxCollection as $canceledTax) {
                $taxTotal -= (float) $canceledTax->getAmount();
            }
        }

        return $taxTotal;
    }

    /**
     * Returns total price excluding tax of specified Magento order including only Mirakl items
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    public function getMiraklTotalPriceExclTax(OrderModel $order, MiraklOrder $miraklOrder)
    {
        return $this->getMiraklSubtotalPriceExclTax($order, $miraklOrder)
             + $this->getMiraklShippingPriceExclTax($order, $miraklOrder);
    }

    /**
     * Returns Mirakl order total of specified order item fields
     *
     * @param OrderModel  $order
     * @param MiraklOrder $miraklOrder
     * @param array       $orderItemFields
     * @return float
     */
    private function getMiraklTotal(OrderModel $order, MiraklOrder $miraklOrder, array $orderItemFields)
    {
        $total = 0;

        foreach ($order->getItemsCollection() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getMiraklOfferId()) {
                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines() as $orderLine) {
                    if ($orderLine->getOffer() && $orderLine->getOffer()->getId() == $item->getMiraklOfferId()) {
                        foreach ($orderItemFields as $field) {
                            $total += $item->getData($field);
                        }
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Returns Mirakl order associated with specified order commercial id and remote id
     *
     * @param string $commercialId
     * @param string $remoteId
     * @return MiraklOrder
     */
    public function getMiraklOrderById($commercialId, $remoteId)
    {
        $locale = $this->coreConfig->getLocale();
        $miraklOrders = $this->orderApiHelper->getOrdersByCommercialId($commercialId, false, $locale);
        foreach ($miraklOrders as $miraklOrder) {
            /** @var MiraklOrder $miraklOrder */
            if ($miraklOrder->getId() == $remoteId) {
                return $miraklOrder;
            }
        }

        return null;
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorBaseGrandTotalInclTax(OrderModel $order)
    {
        $baseGrandTotalInclTax = $order->getBaseGrandTotal();

        foreach ($order->getAllItems() as $item) {
            if (!$item->getMiraklShopId() || $item->getParentItem()) {
                continue;
            }
            $baseGrandTotalInclTax -= $item->getBaseRowTotalInclTax();
        }

        return $baseGrandTotalInclTax - $this->getOperatorBaseShippingInclTax($order);
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorBaseShippingExclTax(OrderModel $order)
    {
        return $order->getBaseShippingAmount();
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorBaseShippingInclTax(OrderModel $order)
    {
        return $order->getBaseShippingInclTax();
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorGrandTotalInclTax(OrderModel $order)
    {
        $grandTotalInclTax = $order->getGrandTotal();

        foreach ($order->getAllItems() as $item) {
            if (!$item->getMiraklShopId() || $item->getParentItem()) {
                continue;
            }
            $grandTotalInclTax -= $item->getRowTotalInclTax();
        }

        return $grandTotalInclTax - $order->getMiraklShippingInclTax();
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorShippingExclTax(OrderModel $order)
    {
        return $order->getShippingAmount();
    }

    /**
     * @param OrderModel $order
     * @return float
     */
    public function getOperatorShippingInclTax(OrderModel $order)
    {
        return $order->getShippingInclTax();
    }

    /**
     * Returns shipping description of specified order including Mirakl order items
     *
     * @param OrderModel $order
     * @return string
     */
    public function getShippingDescription(OrderModel $order)
    {
        $labels = [];

        if (!$order->getId()) {
            return $order->getData(OrderInterface::SHIPPING_DESCRIPTION);
        }

        if (!$order->getMiraklSent() || $this->isAdmin()) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getMiraklShopId()) {
                    $labels[] = $item->getMiraklShippingTypeLabel();
                }
            }
        }

        if (!$this->isFullMiraklOrder($order)) {
            array_unshift($labels, $order->getData(OrderInterface::SHIPPING_DESCRIPTION));
        }

        return implode(', ', array_unique(array_filter($labels)));
    }

    /**
     * @return bool
     */
    private function isAdmin()
    {
        return $this->appState->getAreaCode() == 'adminhtml';
    }

    /**
     * Returns true if the given Magento order contains ONLY Mirakl offers
     *
     * @param OrderModel $order
     * @return bool
     */
    public function isFullMiraklOrder(OrderModel $order)
    {
        foreach ($order->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if (!$item->isDeleted() && !$item->getParentItemId() && !$item->getMiraklShopId()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if given order line has an incident open
     *
     * @param OrderLine $orderLine
     * @return bool
     */
    public function isOrderLineIncident(OrderLine $orderLine)
    {
        return $orderLine->getStatus() && $orderLine->getStatus()->getState() == ReasonType::INCIDENT_OPEN;
    }

    /**
     * Returns true if given order line has been refused by the seller
     *
     * @param OrderLine $orderLine
     * @return bool
     */
    public function isOrderLineRefused(OrderLine $orderLine)
    {
        return $orderLine->getStatus() && $orderLine->getStatus()->getState() == ReasonType::REFUSED;
    }

    /**
     * Returns true if the given Magento order contains SOME Mirakl offers
     *
     * @param OrderModel $order
     * @return bool
     */
    public function isMiraklOrder(OrderModel $order)
    {
        foreach ($order->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if (!$item->isDeleted() && !$item->getParentItemId() && $item->getMiraklShopId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param OrderModel $order
     * @return bool
     */
    public function isShippingPricesIncludeTax(OrderModel $order)
    {
        if ($order->hasData('mirakl_is_shipping_incl_tax')) {
            return (bool) $order->getData('mirakl_is_shipping_incl_tax');
        }

        return $this->connectorConfig->getShippingPricesIncludeTax($order->getStoreId());
    }

    /**
     * @param MiraklOrder $miraklOrder
     * @return bool
     */
    public function canReceiveOrder(MiraklOrder $miraklOrder)
    {
        if (
            $miraklOrder->getPaymentWorkflow() != PaymentWorkflow::PAY_ON_DUE_DATE
            && !$miraklOrder->getCustomerDebitedDate()
        ) {
            return false;
        }

        return in_array(
            $miraklOrder->getStatus()->getState(),
            [OrderState::SHIPPING, OrderState::SHIPPED, OrderState::TO_COLLECT]
        );
    }
}
