<?php
namespace Mirakl\GraphQl\Plugin\Model\Cart\MergeCarts;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryQuoteGraphQl\Model\Cart\MergeCarts\CartQuantityValidator;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Mirakl\Connector\Helper\Offer as OfferHelper;
use Mirakl\Connector\Helper\Quote as MiraklQuoteHelper;

class CartQuantityValidatorPlugin
{
    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var MiraklQuoteHelper
     */
    private $quoteHelper;

    /**
     * @var OfferHelper
     */
    private $offerHelper;

    /**
     * @param CartItemRepositoryInterface   $cartItemRepository
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockIdForCurrentWebsite   $getStockIdForCurrentWebsite
     * @param OfferHelper                   $offerHelper
     * @param MiraklQuoteHelper             $quoteHelper
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        OfferHelper $offerHelper,
        MiraklQuoteHelper $quoteHelper
    ) {
        $this->cartItemRepository = $cartItemRepository;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->offerHelper = $offerHelper;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Validate combined cart quantities to make sure they are within available stock
     *
     * @param CartQuantityValidator $subject
     * @param \Closure              $proceed
     * @param CartInterface         $customerCart
     * @param CartInterface         $guestCart
     * @return bool
     */
    public function aroundValidateFinalCartQuantities(
        CartQuantityValidator $subject,
        \Closure $proceed,
        CartInterface $customerCart,
        CartInterface $guestCart
    ) {
        // We do nothing if source cart is not a Mirakl cart
        if (!$this->quoteHelper->isMiraklQuote($guestCart)) {
            return $proceed($customerCart, $guestCart);
        }

        $modified = false;
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        /** @var CartItemInterface $guestCartItem */
        foreach ($guestCart->getAllVisibleItems() as $guestCartItem) {
            foreach ($customerCart->getAllItems() as $customerCartItem) {
                if (!$customerCartItem->compare($guestCartItem)) {
                    continue;
                }
                if ($customerCartItem->getMiraklOfferId()) { // Handle Mirakl items
                    $offer = $this->offerHelper->getOfferById($customerCartItem->getMiraklOfferId());
                    $deleteItem = !$offer->getId() || ($offer->getQty() < $guestCartItem->getQty() + $customerCartItem->getQty());
                } else {
                    $product = $customerCartItem->getProduct();
                    $productSalableQty = $this->getProductSalableQty->execute($product->getSku(), $stockId);
                    $deleteItem = $productSalableQty < $guestCartItem->getQty() + $customerCartItem->getQty();
                }
                if ($deleteItem) {
                    try {
                        $this->cartItemRepository->deleteById($guestCart->getId(), $guestCartItem->getItemId());
                        $modified = true;
                    } catch (NoSuchEntityException $e) {
                        continue;
                    } catch (CouldNotSaveException $e) {
                        continue;
                    }
                }
            }
        }

        return $modified;
    }
}
