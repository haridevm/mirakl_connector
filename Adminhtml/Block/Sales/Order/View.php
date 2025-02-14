<?php

declare(strict_types=1);

namespace Mirakl\Adminhtml\Block\Sales\Order;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * Add some buttons for Mirakl
     *
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->isMiraklOrder()) {
            $confirmSendOrderDialog = json_encode([
                'message' => __('Are you sure? This order will be sent to Mirakl platform.'),
                'url' => $this->getSendMiraklUrl(),
            ]);
            // phpcs:ignore Generic.Files.LineLength.TooLong
            $onclickJs = "jQuery('#mirakl_send_order').confirmSendOrder($confirmSendOrderDialog).confirmSendOrder('showDialog');";

            $this->addButton('mirakl_send_order', [
                'label' => __('Send to Mirakl'),
                'class' => 'mirakl',
                'onclick' => $onclickJs,
                'data_attribute' => [
                    'mage-init' => '{"confirmSendOrder":{}}',
                ]
            ]);
        }
    }

    /**
     * Add a message if order has already been sent to Mirakl
     *
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        if ($this->isMiraklOrder() && $this->getOrder()->getMiraklSent()) {
            $this->getLayout()
                ->getMessagesBlock()
                ->addNotice(__(
                    'This order has been sent to Mirakl with commercial id: %1.',
                    $this->getOrder()->getIncrementId()
                ));
        }

        parent::_prepareLayout();

        return $this;
    }

    /**
     * @return string
     */
    public function getSendMiraklUrl()
    {
        return $this->getUrl('mirakl/order/send', ['order_id' => $this->getOrderId()]);
    }

    /**
     * @return bool
     */
    protected function isMiraklOrder()
    {
        return $this->getOrder()->getMiraklShippingZone();
    }
}
