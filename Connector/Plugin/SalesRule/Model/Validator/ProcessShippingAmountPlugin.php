<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\SalesRule\Model\Validator;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator;
use Mirakl\Connector\Helper\Quote as QuoteHelper;

/**
 * Magento encapsulates shipping discount calculation in the processShippingAmount() method for all action types
 * We don't change the native calculation logic, we just exclude Mirakl items for mixed carts
 */
class ProcessShippingAmountPlugin
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    /**
     * @var Utility
     */
    private $validatorUtility;

    /**
     * @var RulesApplier
     */
    private $rulesApplier;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartFixedDiscount      $cartFixedDiscountHelper
     * @param Utility                $validatorUtility
     * @param RulesApplier           $rulesApplier
     * @param QuoteHelper            $quoteHelper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CartFixedDiscount $cartFixedDiscountHelper,
        Utility $validatorUtility,
        RulesApplier $rulesApplier,
        QuoteHelper $quoteHelper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->cartFixedDiscountHelper = $cartFixedDiscountHelper;
        $this->validatorUtility = $validatorUtility;
        $this->rulesApplier = $rulesApplier;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * @param Validator $subject
     * @param \Closure  $proceed
     * @param Address   $address
     * @return Validator
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aroundProcessShippingAmount(
        Validator $subject,
        \Closure $proceed,
        Address $address
    ) {
        if (!$subject->getRules($address)) {
            return $proceed($address);
        }
        $quote = $address->getQuote();
        // We do nothing if not Mirakl quote
        if (!$this->quoteHelper->isMiraklQuote($quote)) {
            return $proceed($address);
        }

        $shippingAmount = $address->getShippingAmountForDiscount();
        if (!empty($shippingAmount)) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote = $address->getQuote();
        $appliedRuleIds = [];
        foreach ($subject->getRules($address) as $rule) {
            /* @var Rule $rule */
            if (!$rule->getApplyToShipping() || !$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rulePercent = min(100, $rule->getDiscountAmount());
            switch ($rule->getSimpleAction()) {
                case Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100 - $rule->getDiscountAmount());
                // break is intentionally omitted
                // no break
                case Rule::BY_PERCENT_ACTION:
                    $discountAmount = ($shippingAmount - $address->getShippingDiscountAmount()) * $rulePercent / 100;
                    $baseDiscountAmount = ($baseShippingAmount -
                            $address->getBaseShippingDiscountAmount()) * $rulePercent / 100;
                    $discountPercent = min(100, $address->getShippingDiscountPercent() + $rulePercent);
                    $address->setShippingDiscountPercent($discountPercent);
                    break;
                case Rule::TO_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $discountAmount = $shippingAmount - $quoteAmount;
                    $baseDiscountAmount = $baseShippingAmount - $rule->getDiscountAmount();
                    break;
                case Rule::BY_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $discountAmount = $quoteAmount;
                    $baseDiscountAmount = $rule->getDiscountAmount();
                    break;
                case Rule::CART_FIXED_ACTION:
                    $cartRules = $address->getCartFixedRules();
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $isAppliedToShipping = (int) $rule->getApplyToShipping();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $shippingQuoteAmount = (float) $address->getShippingAmount()
                            - (float) $address->getShippingDiscountAmount();
                        $quoteBaseSubtotal = (float) $this->getBaseQuoteSubtotal($quote);
                        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
                        if ($isAppliedToShipping) {
                            $quoteBaseSubtotal = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                                $this->cartFixedDiscountHelper->getQuoteTotalsForMultiShipping($quote) :
                                $this->cartFixedDiscountHelper->getQuoteTotalsForRegularShipping(
                                    $address,
                                    $quoteBaseSubtotal,
                                    $shippingQuoteAmount
                                );
                            $discountAmount = $this->cartFixedDiscountHelper->
                            getShippingDiscountAmount(
                                $rule,
                                $shippingQuoteAmount,
                                $quoteBaseSubtotal
                            );
                            $baseDiscountAmount = $discountAmount;
                        } else {
                            $discountAmount = min($shippingQuoteAmount, $quoteAmount);
                            $baseDiscountAmount = min(
                                $baseShippingAmount - $address->getBaseShippingDiscountAmount(),
                                $cartRules[$rule->getId()]
                            );
                        }
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;
                case Rule::BUY_X_GET_Y_ACTION:
                    $allQtyDiscount = $this->getDiscountQtyAllItemsBuyXGetYAction($quote, $rule);
                    $quoteAmount = $address->getBaseShippingAmount() / $this->getItemsQty($quote) * $allQtyDiscount;
                    $discountAmount = $this->priceCurrency->convert($quoteAmount, $quote->getStore());
                    $baseDiscountAmount = $quoteAmount;
                    break;
            }

            $discountAmount = min($address->getShippingDiscountAmount() + $discountAmount, $shippingAmount);
            $baseDiscountAmount = min(
                $address->getBaseShippingDiscountAmount() + $baseDiscountAmount,
                $baseShippingAmount
            );
            $address->setShippingDiscountAmount($this->priceCurrency->roundPrice($discountAmount));
            $address->setBaseShippingDiscountAmount($this->priceCurrency->roundPrice($baseDiscountAmount));
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->rulesApplier->maintainAddressCouponCode($address, $rule, $subject->getCouponCode());
            $this->rulesApplier->addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $address->setAppliedRuleIds($this->validatorUtility->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->validatorUtility->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $subject;
    }

    /**
     * @param Quote $quote
     * @param Rule  $rule
     * @return float
     */
    private function getDiscountQtyAllItemsBuyXGetYAction(Quote $quote, Rule $rule)
    {
        $discountAllQty = 0;
        foreach ($quote->getItems() as $item) {
            $qty = $item->getQty();

            $discountStep = $rule->getDiscountStep();
            $discountAmount = $rule->getDiscountAmount();
            if (!$discountStep || $discountAmount > $discountStep || $item->getMiraklOfferId()) {
                continue;
            }
            $buyAndDiscountQty = $discountStep + $discountAmount;

            $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
            $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

            $discountQty = $fullRuleQtyPeriod * $discountAmount;
            if ($freeQty > $discountStep) {
                $discountQty += $freeQty - $discountStep;
            }

            $discountAllQty += $discountQty;
        }

        return $discountAllQty;
    }

    /**
     * @param Quote $quote
     * @return int
     */
    private function getItemsQty(Quote $quote)
    {
        $itemsQty = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getMiraklOfferId()) {
                $itemsQty += $item->getQty();
            }
        }

        return $itemsQty;
    }

    /**
     * @param Quote $quote
     * @return float
     */
    private function getBaseQuoteSubtotal(Quote $quote)
    {
        $baseSubtotal = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getMiraklOfferId()) {
                $baseSubtotal += $item->getBaseRowTotal();
            }
        }

        return $baseSubtotal;
    }
}
