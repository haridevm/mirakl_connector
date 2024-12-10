<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\System\Config\Source\Order;

use Magento\Framework\Data\OptionSourceInterface;
use Mirakl\MMP\Common\Domain\Payment\PaymentWorkflow;

class Payment implements OptionSourceInterface
{
    /**
     * Retrieves order payment workflow types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaymentWorkflow::PAY_ON_ACCEPTANCE,
                'label' => PaymentWorkflow::PAY_ON_ACCEPTANCE
            ],
            [
                'value' => PaymentWorkflow::PAY_ON_DELIVERY,
                'label' => PaymentWorkflow::PAY_ON_DELIVERY
            ],
            [
                'value' => PaymentWorkflow::PAY_ON_SHIPMENT,
                'label' => PaymentWorkflow::PAY_ON_SHIPMENT
            ],
            [
                'value' => PaymentWorkflow::NO_CUSTOMER_PAYMENT_CONFIRMATION,
                'label' => PaymentWorkflow::NO_CUSTOMER_PAYMENT_CONFIRMATION
            ],
        ];
    }
}
