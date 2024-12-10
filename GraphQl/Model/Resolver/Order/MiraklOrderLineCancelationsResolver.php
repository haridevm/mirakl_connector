<?php
declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;

class MiraklOrderLineCancelationsResolver implements ResolverInterface
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

        $cancelations = [];
        foreach ($miraklOrderLine->getCancelations() as $cancelation) {
            /** @var \Mirakl\MMP\Common\Domain\Order\Cancelation $cancelation */
            $cancelationData = [
                'quantity'        => $cancelation->getQuantity(),
                'amount'          => ['value' => $cancelation->getAmount(), 'currency' => $currency],
                'shipping_amount' => ['value' => $cancelation->getShippingAmount(), 'currency' => $currency]
            ];
            /** @var \Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount $tax */
            foreach ($cancelation->getTaxes() as $tax) {
                $cancelationData['taxes'][] = [
                    'amount' => ['value' => $tax->getAmount(), 'currency' => $currency],
                    'code'   => $tax->getCode(),
                ];
            }
            foreach ($cancelation->getShippingTaxes() as $tax) {
                $cancelationData['shipping_taxes'][] = [
                    'amount' => ['value' => $tax->getAmount(), 'currency' => $currency],
                    'code'   => $tax->getCode(),
                ];
            }
            $cancelationData['model'] = $cancelation;
            $cancelations[] = $cancelationData;
        }

        return $cancelations;
    }
}

