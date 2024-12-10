<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirakl\MMP\Common\Domain\Order\OrderState;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;

class MiraklOrderLinesResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var MiraklOrder $order */
        $miraklOrder = $value['model'];
        $currency = $miraklOrder->getCurrencyIsoCode();

        $orderLinesData = [];
        foreach ($miraklOrder->getOrderLines() as $orderLine) {
            /** @var MiraklOrder\OrderLine $orderLine */
            $orderLineData = [
                'order_line_id'     => $orderLine->getId(),
                'offer_id'          => $orderLine->getOffer()->getId(),
                'product_sku'       => $orderLine->getOffer()->getSku(),
                'status'            => $orderLine->getStatus()->getState(),
                'quantity'          => $orderLine->getQuantity(),
                'price'             => ['value' => $orderLine->getPrice(), 'currency' => $currency],
                'shipping_price'    => ['value' => $orderLine->getShippingPrice(), 'currency' => $currency],
                'total_price'       => ['value' => $orderLine->getTotalPrice(), 'currency' => $currency],
                'can_open_incident' => $orderLine->getCanOpenIncident(),
                'is_refused'        => $orderLine->getStatus()->getState() === OrderState::REFUSED,
            ];
            foreach ($orderLine->getTaxes() as $tax) {
                /** @var \Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount $tax */
                $orderLineData['taxes'][] = [
                    'amount' => ['value' => $tax->getAmount(), 'currency' => $currency],
                    'code'   => $tax->getCode(),
                ];
            }
            foreach ($orderLine->getShippingTaxes() as $shippingTax) {
                /** @var \Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount $shippingTax */
                $orderLineData['shipping_taxes'][] = [
                    'amount' => ['value' => $shippingTax->getAmount(), 'currency' => $currency],
                    'code'   => $shippingTax->getCode(),
                ];
            }
            $orderLineData['currency'] = $miraklOrder->getCurrencyIsoCode();
            $orderLineData['model'] = $orderLine;
            $orderLinesData[] = $orderLineData;
        }

        return $orderLinesData;
    }
}
