<?php
declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Sales\Api\Data\OrderItemInterface;

class OrderItemMarketplaceInfoResolver implements ResolverInterface
{
    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @param  JsonSerializer $jsonSerializer
     * @param  Serialize      $serializer
     */
    public function __construct(
        JsonSerializer $jsonSerializer,
        Serialize $serializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderItemInterface $orderItem */
        $orderItem = $value['model'];

        $currency = $orderItem->getOrder()->getOrderCurrencyCode();
        $baseCurrency = $orderItem->getOrder()->getBaseCurrencyCode();

        $shippingTaxApplied = $orderItem->getMiraklShippingTaxApplied()
            ? $this->jsonSerializer->serialize($this->serializer->unserialize($orderItem->getMiraklShippingTaxApplied()))
            : null;

        $customTaxApplied = $orderItem->getMiraklCustomShippingTaxApplied()
            ? $this->jsonSerializer->serialize($this->serializer->unserialize($orderItem->getMiraklCustomShippingTaxApplied()))
            : null;

        return [
            'offer_id'                        => $orderItem->getMiraklOfferId(),
            'shop_id'                         => $orderItem->getMiraklShopId(),
            'shop_name'                       => $orderItem->getMiraklShopName(),
            'leadtime_to_ship'                => $orderItem->getMiraklLeadtimeToShip(),
            'shipping_type'                   => $orderItem->getMiraklShippingType(),
            'base_shipping_excl_tax'          => ['value' => $orderItem->getMiraklBaseShippingExclTax(), 'currency' => $baseCurrency],
            'base_shipping_incl_tax'          => ['value' => $orderItem->getMiraklBaseShippingInclTax(), 'currency' => $baseCurrency],
            'shipping_excl_tax'               => ['value' => $orderItem->getMiraklShippingExclTax(), 'currency' => $currency],
            'shipping_incl_tax'               => ['value' => $orderItem->getMiraklShippingInclTax(), 'currency' => $currency],
            'shipping_tax_percent'            => $orderItem->getMiraklShippingTaxPercent(),
            'base_shipping_tax_amount'        => ['value' => $orderItem->getMiraklBaseShippingTaxAmount(), 'currency' => $baseCurrency],
            'shipping_tax_amount'             => ['value' => $orderItem->getMiraklShippingTaxAmount(), 'currency' => $currency],
            'shipping_tax_applied'            => $shippingTaxApplied,
            'custom_tax_applied'              => $customTaxApplied,
            'base_custom_shipping_tax_amount' => ['value' => $orderItem->getMiraklBaseCustomShippingTaxAmount(), 'currency' => $baseCurrency],
            'custom_shipping_tax_amount'      => ['value' => $orderItem->getMiraklCustomShippingTaxAmount(), 'currency' => $currency],
            'model'                           => $orderItem
        ];
    }
}
