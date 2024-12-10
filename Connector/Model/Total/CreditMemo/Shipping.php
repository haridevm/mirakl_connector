<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Total\CreditMemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * This code is directly inspired from this Magento class:
 * @see \Magento\Sales\Model\Order\Creditmemo\Total\Shipping
 *
 * The goal is to calculate the tax amount to refund according to
 * the shipping amount to refund.
 */
class Shipping extends AbstractTotal
{
    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param TaxConfig              $taxConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param array                  $data
     */
    public function __construct(
        TaxConfig $taxConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($data);
        $this->taxConfig = $taxConfig;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Creditmemo $creditMemo
     * @return $this
     * @throws LocalizedException
     */
    public function collect(Creditmemo $creditMemo)
    {
        $order = $creditMemo->getOrder();

        if (!$order->getMiraklShippingExclTax()) {
            return $this;
        }

        $miraklShippingExclTax     = $order->getMiraklShippingExclTax();
        $miraklBaseShippingExclTax = $order->getMiraklBaseShippingExclTax();
        $miraklShippingInclTax     = $order->getMiraklShippingInclTax();
        $miraklBaseShippingInclTax = $order->getMiraklBaseShippingInclTax();
        $miraklShippingTaxAmount   = $order->getMiraklShippingTaxAmount() + $order->getMiraklCustomShippingTaxAmount();

        // Amounts excluding tax
        $allowedAmount = $miraklShippingExclTax - $order->getMiraklShippingRefunded();
        $baseAllowedAmount = $miraklBaseShippingExclTax - $order->getMiraklBaseShippingRefunded();

        // Amounts including tax
        $allowedTaxAmount = $miraklShippingTaxAmount - $order->getMiraklShippingTaxRefunded();
        $allowedAmountInclTax = $allowedAmount + $allowedTaxAmount;
        $baseAllowedAmountInclTax = $miraklBaseShippingInclTax
            - $order->getMiraklBaseShippingRefunded()
            - $order->getMiraklBaseShippingTaxRefunded();

        if ($creditMemo->hasMiraklBaseShippingAmount()) {
            // For the conditional logic, we will either use amounts that always include tax -OR- never include tax.
            // The logic uses the 'base' currency to be consistent with what the user (admin) provided as input.
            $useAmountsWithTax = $this->isSuppliedShippingAmountInclTax($order);

            // Since the user (admin) supplied 'desiredAmount' it already has tax -OR- does not include tax
            $desiredAmount = $this->priceCurrency->roundPrice($creditMemo->getMiraklBaseShippingAmount());
            $maxAllowedAmount = ($useAmountsWithTax ? $baseAllowedAmountInclTax : $baseAllowedAmount);
            $originalTotalAmount = ($useAmountsWithTax ? $miraklBaseShippingInclTax : $miraklShippingExclTax);

            // Note: ($x < $y + 0.0001) means ($x <= $y) for floats
            if ($desiredAmount < $this->priceCurrency->roundPrice($maxAllowedAmount) + 0.0001) {
                // since the admin is returning less than the allowed amount, compute the ratio being returned
                $ratio = 0;
                if ($originalTotalAmount > 0) {
                    $ratio = $desiredAmount / $originalTotalAmount;
                }
                // capture amounts without tax
                // Note: ($x > $y - 0.0001) means ($x >= $y) for floats
                if ($desiredAmount > $maxAllowedAmount - 0.0001) {
                    $shippingAmount = $allowedAmount;
                    $baseShippingAmount = $baseAllowedAmount;
                } else {
                    $shippingAmount = $this->priceCurrency->roundPrice($miraklShippingExclTax * $ratio);
                    $baseShippingAmount = $this->priceCurrency->roundPrice($miraklBaseShippingExclTax * $ratio);
                }
                $shippingInclTax = $this->priceCurrency->roundPrice($miraklShippingInclTax * $ratio);
                $baseShippingInclTax = $this->priceCurrency->roundPrice($miraklBaseShippingInclTax * $ratio);
            } else {
                $maxAllowedAmount = $order->getBaseCurrency()->format($maxAllowedAmount, null, false);
                throw new LocalizedException(
                    __('Maximum shipping amount allowed to refund is: %1', $maxAllowedAmount)
                );
            }
        } else {
            $shippingAmount = $allowedAmount;
            $baseShippingAmount = $baseAllowedAmount;
            $shippingInclTax = $this->priceCurrency->roundPrice($allowedAmountInclTax);
            $baseShippingInclTax = $this->priceCurrency->roundPrice($baseAllowedAmountInclTax);
        }

        /** @var \Magento\Sales\Api\Data\CreditmemoExtension $extensionAttributes */
        $extensionAttributes = $creditMemo->getExtensionAttributes();
        $extensionAttributes->setMiraklShippingExclTax($shippingAmount);
        $extensionAttributes->setMiraklBaseShippingExclTax($baseShippingAmount);
        $extensionAttributes->setMiraklShippingInclTax($shippingInclTax);
        $extensionAttributes->setMiraklBaseShippingInclTax($baseShippingInclTax);

        $creditMemo->setGrandTotal($creditMemo->getGrandTotal() + $shippingAmount);
        $creditMemo->setBaseGrandTotal($creditMemo->getBaseGrandTotal() + $baseShippingAmount);

        return $this;
    }

    /**
     * Returns whether the user specified a shipping amount that already includes tax
     *
     * @param Order $order
     * @return bool
     */
    private function isSuppliedShippingAmountInclTax(Order $order)
    {
        return $this->taxConfig->displaySalesShippingInclTax($order->getStoreId());
    }
}
