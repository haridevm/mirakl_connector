<?php
declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;

class OrderMarketplaceInfoResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $order */
        $order = $value['model'];

        $baseCurrency = $order->getBaseCurrencyCode();
        $currency = $order->getOrderCurrencyCode();

        return [
            'sent'                            => $order->getMiraklSent(),
            'shipping_zone'                   => $order->getMiraklShippingZone(),
            'base_shipping_excl_tax'          => ['value' => $order->getMiraklBaseShippingExclTax(), 'currency' => $baseCurrency],
            'base_shipping_incl_tax'          => ['value' => $order->getMiraklBaseShippingInclTax(), 'currency' => $baseCurrency],
            'shipping_excl_tax'               => ['value' => $order->getMiraklShippingExclTax(), 'currency' => $currency],
            'shipping_incl_tax'               => ['value' => $order->getMiraklShippingInclTax(), 'currency' => $currency],
            'base_shipping_tax_amount'        => ['value' => $order->getMiraklBaseShippingTaxAmount(), 'currency' => $baseCurrency],
            'shipping_tax_amount'             => ['value' => $order->getMiraklShippingTaxAmount(), 'currency' => $currency],
            'base_custom_shipping_tax_amount' => ['value' => $order->getMiraklBaseCustomShippingTaxAmount(), 'currency' => $baseCurrency],
            'custom_shipping_tax_amount'      => ['value' => $order->getMiraklCustomShippingTaxAmount(), 'currency' => $currency],
            'is_offer_incl_tax'               => $order->getMiraklIsOfferInclTax(),
            'is_shipping_incl_tax'            => $order->getMiraklIsShippingInclTax(),
            'model'                           => $order,
        ];
    }
}
