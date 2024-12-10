<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Order;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class GetOrderCustomerId
{
    /**
     * 50 chars is the limit in Mirakl for order customer id
     *
     * @var int
     */
    private int $limit;

    /**
     * @param int $limit
     */
    public function __construct(int $limit = 50)
    {
        $this->limit = $limit;
    }

    /**
     * @param OrderInterface         $order
     * @param CustomerInterface|null $customer
     * @return string
     */
    public function execute(OrderInterface $order, ?CustomerInterface $customer = null): string
    {
        if (!$order->getCustomerIsGuest() && $customer && $customer->getCreatedAt() < $order->getCreatedAt()) {
            // Customer account has not been attached to order after order creation
            $customerId = $order->getCustomerId();
        } else {
            $customerId = $order->getCustomerEmail();
            if (mb_strlen($customerId) > $this->limit) {
                // Encode the customer id if it is too long
                $customerId = sha1($customerId);
            }
        }

        return (string) $customerId;
    }
}
