<?php
namespace Mirakl\Connector\Model\Quote\Address\Total;

use Magento\Catalog\Model\Product;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mirakl\Connector\Helper\Quote as QuoteHelper;
use Mirakl\Connector\Model\Quote\Synchronizer as QuoteSynchronizer;

class Shipping extends Address\Total\AbstractTotal
{
    /**
     * @var QuoteHelper
     */
    protected $quoteHelper;

    /**
     * @var QuoteSynchronizer
     */
    protected $quoteSynchronizer;

    /**
     * @param   QuoteHelper         $quoteHelper
     * @param   QuoteSynchronizer   $quoteSynchronizer
     */
    public function __construct(QuoteHelper $quoteHelper, QuoteSynchronizer $quoteSynchronizer)
    {
        $this->quoteHelper = $quoteHelper;
        $this->quoteSynchronizer = $quoteSynchronizer;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Address\Total $total
    ) {
        if (!$this->quoteHelper->isMiraklQuote($quote)) {
            return $this;
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping) {
            $address = $shipping->getAddress();
            // Do not apply Mirakl shipping fees if address is not of type 'shipping'
            if ($address && $address->getAddressType() != 'shipping') {
                return $this;
            }
        }

        $items = $quote->getAllVisibleItems();

        $shippingFeeBaseTotal       = 0;
        $shippingFeeTotal           = 0;
        $shippingExclTaxBaseTotal   = 0;
        $shippingExclTaxTotal       = 0;
        $shippingInclTaxBaseTotal   = 0;
        $shippingInclTaxTotal       = 0;
        $shippingTaxBaseTotal       = 0;
        $shippingTaxTotal           = 0;
        $customShippingTaxBaseTotal = 0;
        $customShippingTaxTotal     = 0;

        /** @var QuoteItem $item */
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item->getProduct();
            if ($product->isVirtual() || $item->getParentItem() || !$item->getMiraklShopId()) {
                continue;
            }
            $shippingFeeBaseTotal       += $item->getMiraklBaseShippingFee(); // @deprecated
            $shippingFeeTotal           += $item->getMiraklShippingFee(); // @deprecated
            $shippingExclTaxBaseTotal   += $item->getMiraklBaseShippingExclTax();
            $shippingExclTaxTotal       += $item->getMiraklShippingExclTax();
            $shippingInclTaxBaseTotal   += $item->getMiraklBaseShippingInclTax();
            $shippingInclTaxTotal       += $item->getMiraklShippingInclTax();
            $shippingTaxBaseTotal       += $item->getMiraklBaseShippingTaxAmount();
            $shippingTaxTotal           += $item->getMiraklShippingTaxAmount();
            $customShippingTaxBaseTotal += $item->getMiraklBaseCustomShippingTaxAmount();
            $customShippingTaxTotal     += $item->getMiraklCustomShippingTaxAmount();
        }

        $zone = $this->quoteSynchronizer->getQuoteShippingZone($quote);

        $quote->setMiraklShippingZone($zone)
            ->setMiraklBaseShippingFee($shippingFeeBaseTotal) // @deprecated
            ->setMiraklShippingFee($shippingFeeTotal) // @deprecated
            ->setMiraklBaseShippingExclTax($shippingExclTaxBaseTotal)
            ->setMiraklShippingExclTax($shippingExclTaxTotal)
            ->setMiraklBaseShippingInclTax($shippingInclTaxBaseTotal)
            ->setMiraklShippingInclTax($shippingInclTaxTotal)
            ->setMiraklBaseShippingTaxAmount($shippingTaxBaseTotal)
            ->setMiraklShippingTaxAmount($shippingTaxTotal)
            ->setMiraklBaseCustomShippingTaxAmount($customShippingTaxBaseTotal)
            ->setMiraklCustomShippingTaxAmount($customShippingTaxTotal);

        $total->setBaseTotalAmount($this->getCode(), $shippingExclTaxBaseTotal);
        $total->setTotalAmount($this->getCode(), $shippingExclTaxTotal);

        $total->addBaseTotalAmount('tax', $shippingTaxBaseTotal + $customShippingTaxBaseTotal);
        $total->addTotalAmount('tax', $shippingTaxTotal + $customShippingTaxTotal);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Quote $quote, Address\Total $total)
    {
        return [
            'code'  => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $quote->getMiraklShippingExclTax(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Marketplace Shipping');
    }
}