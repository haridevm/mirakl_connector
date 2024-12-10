<?php
declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Mirakl\Connector\Helper\Order as MiraklOrderHelper;
use Mirakl\GraphQl\Model\Order\MiraklOrderProvider;
use Mirakl\GraphQl\Model\Resolver\AbstractResolver;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;

class MiraklOrdersResolver extends AbstractResolver implements ResolverInterface
{
    /**
     * @var MiraklOrderProvider
     */
    protected $miraklOrderProvider;

    /**
     * @var MiraklOrderHelper
     */
    protected $miraklOrderHelper;

    /**
     * @param  MiraklOrderProvider $miraklOrderProvider
     * @param  MiraklOrderHelper   $miraklOrderHelper
     */
    public function __construct(
        MiraklOrderProvider $miraklOrderProvider,
        MiraklOrderHelper $miraklOrderHelper
    ) {
        $this->miraklOrderProvider = $miraklOrderProvider;
        $this->miraklOrderHelper = $miraklOrderHelper;
    }

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

        $orderTaxMode = $this->getInput($args, 'mp_order_tax_mode') ?? MiraklOrderProvider::MIRAKL_ORDER_QUERY_DEFAULT_TAX_MODE;
        $miraklOrders = $this->miraklOrderProvider->getMiraklOrders($order->getIncrementId(), $orderTaxMode);
        $baseCurrency = $order->getBaseCurrencyCode();

        $miraklOrdersData = [];
        foreach ($miraklOrders as $miraklOrder) {
            /** @var MiraklOrder $miraklOrder */
            $deliveryDate = $miraklOrder->getDeliveryDate();
            $miraklOrderData = [
                'order_id'               => $miraklOrder->getId(),
                'status'                 => $miraklOrder->getStatus()->getState(),
                'price'                  => ['value' => $miraklOrder->getPrice(), 'currency' => $baseCurrency],
                'shipping_price'         => ['value' => $miraklOrder->getShipping()->getPrice(), 'currency' => $baseCurrency],
                'total_price'            => ['value' => $miraklOrder->getTotalPrice(), 'currency' => $baseCurrency],
                'shop_id'                => $miraklOrder->getShopId(),
                'shop_name'              => $miraklOrder->getShopName(),
                'order_tax_mode'         => $miraklOrder->getOrderTaxMode(),
                'shipping_tracking_url'  => $miraklOrder->getShipping()->getTrackingUrl(),
                'shipping_tracking'      => $miraklOrder->getShipping()->getTrackingNumber(),
                'shipping_type_code'     => $miraklOrder->getShipping()->getType()->getCode(),
                'shipping_type_label'    => $miraklOrder->getShipping()->getType()->getLabel(),
                'can_cancel'             => $miraklOrder->getCanCancel(),
                'can_evaluate'           => $miraklOrder->getCanEvaluate(),
                'has_incident'           => $miraklOrder->getHasIncident(),
                'has_invoice'            => $miraklOrder->getHasInvoice(),
                'has_customer_messages'  => $miraklOrder->getHasCustomerMessage(),
                'can_validate_receipt'   => $this->miraklOrderHelper->canReceiveOrder($miraklOrder),
                'earliest_delivery_date' => $deliveryDate && $deliveryDate->getEarliest() ? $deliveryDate->getEarliest()->format('Y-m-d H:i:s') : null,
                'latest_delivery_date'   => $deliveryDate && $deliveryDate->getLatest() ? $deliveryDate->getLatest()->format('Y-m-d H:i:s') : null,
                'model'                  => $miraklOrder
            ];

            $miraklOrdersData[] = $miraklOrderData;
        }

        return $miraklOrdersData;
    }
}
