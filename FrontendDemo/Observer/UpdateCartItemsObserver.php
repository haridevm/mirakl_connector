<?php
namespace Mirakl\FrontendDemo\Observer;

use Magento\Framework\Event\Observer;

class UpdateCartItemsObserver extends AbstractObserver
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart $cart */
        /** @var \Magento\Framework\DataObject $info */
        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo();

        if ($cart && $info) {
            $info = $info->getData();

            $this->quoteUpdater->synchronize($cart->getQuote());

            // Synchronize shipping methods of Mirakl offers
            if (isset($info['offers'])) {
                $this->quoteHelper->updateOffersShippingTypes($info['offers'], $cart->getQuote());
            }

            // Update native shipping method if needed
            if (isset($info['estimate_method'])) {
                $cart->getQuote()->getShippingAddress()->setShippingMethod($info['estimate_method']);
            }
        }
    }
}
