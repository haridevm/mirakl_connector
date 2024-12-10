<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Order;

use Mirakl\Api\Helper\Order as OrderApiHelper;
use Mirakl\MMP\FrontOperator\Domain\Collection\Order\OrderCollection as MiraklOrderCollection;

class MiraklOrderProvider
{
    public const MIRAKL_ORDER_QUERY_DEFAULT_TAX_MODE = 'TAX_INCLUDED';

    /**
     * @var OrderApiHelper
     */
    private $orderApiHelper;

    /**
     * @var array
     */
    private $mirakOrders;

    /**
     * @param OrderApiHelper $orderApiHelper
     */
    public function __construct(OrderApiHelper $orderApiHelper)
    {
        $this->orderApiHelper = $orderApiHelper;
    }

    /**
     * @param string $commercialId
     * @param string $orderTaxMode
     * @return MiraklOrderCollection
     */
    public function getMiraklOrders(
        string $commercialId,
        string $orderTaxMode = self::MIRAKL_ORDER_QUERY_DEFAULT_TAX_MODE
    ): MiraklOrderCollection {
        $orderKey = "$commercialId-$orderTaxMode";
        if (!isset($this->mirakOrders[$orderKey])) {
            $params = [
                'commercial_ids' => [$commercialId],
                'order_tax_mode' => $orderTaxMode
            ];
            $orderCollection = $this->orderApiHelper->getOrders($params);
            $this->mirakOrders[$orderKey] = $orderCollection;
        }

        return $this->mirakOrders[$orderKey];
    }
}
