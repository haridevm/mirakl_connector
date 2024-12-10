<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\SalesRule\Model\Condition\Address;

use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\SalesRule\Model\Rule\Condition\Address as AddressCondition;
use Mirakl\Connector\Helper\Quote as QuoteHelper;

/**
 * Some rule conditions are not based on cart items validation but directly on cart attributes
 * Example: (Cart Subtotal >= 50 AND Shipping Country IS France)
 * This type of condition needs to be handled manually to exclude Mirakl items
 *
 * Note: All totals calculated in this plugin will be set only for rule condition validation and won't
 * be saved nor used in quote totals collector
 */
class ValidatePlugin
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @param QuoteHelper $quoteHelper
     */
    public function __construct(QuoteHelper $quoteHelper)
    {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param AddressCondition $subject
     * @param \Closure         $proceed
     * @param AbstractModel    $model
     * @return bool
     */
    public function aroundValidate(
        AddressCondition $subject,
        \Closure $proceed,
        AbstractModel $model
    ) {
        if ($subject instanceof \Mirakl\Core\Model\Shipping\Zone\Condition\Address) {
            return $proceed($model); // Proceed normally if it's a Mirakl shipping zone address condition
        }

        $address = $model;
        if (!$address instanceof QuoteAddress) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        // Address condition is systematically invalid for rule if full Mirakl quote
        if ($this->quoteHelper->isFullMiraklQuote($address->getQuote())) {
            return false;
        }

        // We do nothing if not Mirakl quote
        if (!$this->quoteHelper->isMiraklQuote($address->getQuote())) {
            return $proceed($model);
        }

        $validationAddress = clone $address;

        $validationAddress->setBaseSubtotalWithDiscount(
            $this->getOperatorTotal($validationAddress, 'base_subtotal_with_discount')
        );
        $validationAddress->setBaseSubtotalTotalInclTax(
            $this->getOperatorTotal($validationAddress, 'base_row_total_incl_tax')
        );
        $validationAddress->setBaseSubtotal($this->getOperatorTotal($validationAddress, 'base_row_total'));
        $validationAddress->setTotalQty($this->getOperatorTotal($validationAddress, 'qty'));
        $validationAddress->setTotalWeight($this->getOperatorTotal($validationAddress, 'weight'));

        return $proceed($validationAddress);
    }

    /**
     * @param QuoteAddress $address
     * @param string       $field
     * @return float
     */
    private function getOperatorTotal(QuoteAddress $address, string $field)
    {
        $total = 0;
        foreach ($address->getQuote()->getAllVisibleItems() as $item) {
            if (!$item->getMiraklOfferId()) {
                if ($field === 'base_subtotal_with_discount') {
                    $total += $item->getBaseRowTotal() + $item->getBaseDiscountAmount();
                } else {
                    $total += $item->getData($field);
                }
            }
        }

        return $total;
    }
}
