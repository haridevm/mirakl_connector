<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;

class MiraklOrderLineRefundsResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderLine $orderLine */
        $miraklOrderLine = $value['model'];
        $currency = $value['currency'];

        $refunds = [];
        foreach ($miraklOrderLine->getRefunds() as $refund) {
            /** @var \Mirakl\MMP\Common\Domain\Order\Refund $refund */
            $refundData = [
                'quantity'        => $refund->getQuantity(),
                'amount'          => ['value' => $refund->getAmount(), 'currency' => $currency],
                'shipping_amount' => ['value' => $refund->getShippingAmount(), 'currency' => $currency]
            ];
            foreach ($refund->getTaxes() as $tax) {
                /** @var \Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount $tax */
                $refundData['taxes'][] = [
                    'amount' => ['value' => $tax->getAmount(), 'currency' => $currency],
                    'code'   => $tax->getCode(),
                ];
            }
            foreach ($refund->getShippingTaxes() as $tax) {
                $refundData['shipping_taxes'][] = [
                    'amount' => ['value' => $tax->getAmount(), 'currency' => $currency],
                    'code'   => $tax->getCode(),
                ];
            }
            $refundData['model'] = $refund;
            $refunds[] = $refundData;
        }

        return $refunds;
    }
}
