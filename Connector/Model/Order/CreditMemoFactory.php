<?php
namespace Mirakl\Connector\Model\Order;

use Magento\Sales\Model\Order;

class CreditMemoFactory extends Order\CreditmemoFactory
{
    /**
     * {@inheritdoc}
     */
    protected function initData($creditMemo, $data)
    {
        parent::initData($creditMemo, $data);

        // If an amount is specified in a refund for Mirakl shipping, assign it to the credit memo
        if (isset($data['mirakl_shipping_amount'])) {
            $amount = (float) $data['mirakl_shipping_amount'];
            $creditMemo->setMiraklShippingAmount($amount);
            $creditMemo->setMiraklBaseShippingAmount($amount);
        }
    }
}
