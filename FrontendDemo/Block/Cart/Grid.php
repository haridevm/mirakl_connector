<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Cart;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Mirakl\Connector\Model\Quote\Synchronizer as QuoteSynchronizer;
use Mirakl\Connector\Model\Quote\Updater as QuoteUpdater;
use Mirakl\MMP\Front\Domain\Shipping\ShippingFeeType;

class Grid extends \Magento\Checkout\Block\Cart\Grid
{
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @var QuoteSynchronizer
     */
    private $quoteSynchronizer;

    /**
     * @param Context                $context
     * @param CustomerSession        $customerSession
     * @param CheckoutSession        $checkoutSession
     * @param Url                    $catalogUrlBuilder
     * @param Cart                   $cartHelper
     * @param HttpContext            $httpContext
     * @param CollectionFactory      $itemCollectionFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param QuoteUpdater           $quoteUpdater
     * @param QuoteSynchronizer      $quoteSynchronizer
     * @param array                  $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Url $catalogUrlBuilder,
        Cart $cartHelper,
        HttpContext $httpContext,
        CollectionFactory $itemCollectionFactory,
        JoinProcessorInterface $joinProcessor,
        QuoteUpdater $quoteUpdater,
        QuoteSynchronizer $quoteSynchronizer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $itemCollectionFactory,
            $joinProcessor,
            $data
        );
        $this->quoteUpdater = $quoteUpdater;
        $this->quoteSynchronizer = $quoteSynchronizer;
    }

    /**
     * Some layout customization for Mirakl items renderer (remove wishlist button,..)
     *
     * @param Item $item
     * @return string
     */
    public function getMarketplaceItemRendererHtml(Item $item)
    {
        /** @var  $renderer \Magento\Framework\View\Element\Template */
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        $renderer->getLayout()->unsetElement('checkout.cart.item.renderers.simple.actions.move_to_wishlist')
                              ->unsetElement('checkout.cart.item.renderers.configurable.actions.move_to_wishlist');

        return $renderer->toHtml();
    }

    /**
     * Group operator and Mirakl items apart
     * Group Mirakl items by selected shipping type
     *
     * @return array
     */
    public function getGroupedItems(): array
    {
        $items = $this->quoteSynchronizer->getGroupedItems($this->getQuote());
        $groupedItems = [];
        foreach ($items as $item) {
            if ($item->getMiraklShopId()) {
                $selectedShippingType = $this->getSelectedShippingType($item);
                $shipmentKey = $selectedShippingType->getCode() . '_' . $item->getMiraklLeadtimeToShip();
                $groupedItems[$item->getMiraklShopId()][$shipmentKey][] = $item;
            } else {
                $groupedItems['operator'][] = $item;
            }
        }

        return $groupedItems;
    }

    /**
     * @param Item $item
     * @return ShippingFeeType
     */
    private function getSelectedShippingType(Item $item)
    {
        if ($shippingTypeCode = $item->getMiraklShippingType()) {
            return $this->quoteUpdater->getItemShippingTypeByCode($item, $shippingTypeCode);
        }

        return $this->quoteUpdater->getItemSelectedShippingType($item);
    }
}
