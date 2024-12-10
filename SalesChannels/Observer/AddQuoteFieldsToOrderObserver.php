<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddQuoteFieldsToOrderObserver implements ObserverInterface
{
    /**
     * @var array
     */
    private $copyQuoteFields = [
        'mirakl_channel_code',
    ];

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Quote\Model\Quote $quote
         * @var \Magento\Sales\Model\Order $order
         */
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        // Copy quote fields to order
        foreach ($this->copyQuoteFields as $field) {
            $order->setData($field, $quote->getData($field));
        }
    }
}
