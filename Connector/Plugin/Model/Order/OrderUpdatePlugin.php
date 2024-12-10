<?php
namespace Mirakl\Connector\Plugin\Model\Order;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Mirakl\Connector\Helper\Order as OrderHelper;
use Psr\Log\LoggerInterface;

class OrderUpdatePlugin
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param   OrderHelper     $orderHelper
     * @param   LoggerInterface $logger
     */
    public function __construct(
        OrderHelper $orderHelper,
        LoggerInterface $logger
    ) {
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
    }

    /**
     * Mirakl Order is created in this plugin because order item id used for setOrderLineId may not be set in
     * sales_order_save_after event. @see OrderSavePlugin for new order
     *
     * @param   OrderResourceInterface  $subject
     * @param   \Closure                $proceed
     * @param   AbstractModel           $order
     * @return  OrderResourceInterface
     */
    public function aroundSave(
        OrderResourceInterface $subject,
        \Closure $proceed,
        AbstractModel $order
    ) {
        /** @var Order $order */
        $isNew = $order->isObjectNew();
        $return = $proceed($order);

        if (!$isNew) {
            try {
                $this->orderHelper->autoCreateMiraklOrder($order);
            } catch (\Exception $e) {
                // Ignore to avoid errors in frontend
                $this->logger->warning($e->getMessage());
            }
        }

        return $return;
    }
}
