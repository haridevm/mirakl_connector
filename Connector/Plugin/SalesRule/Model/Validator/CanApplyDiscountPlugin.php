<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\SalesRule\Model\Validator;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;

class CanApplyDiscountPlugin
{
    /**
     * @param Validator    $subject
     * @param \Closure     $proceed
     * @param AbstractItem $item
     * @return bool
     */
    public function aroundCanApplyDiscount(
        Validator $subject,
        \Closure $proceed,
        AbstractItem $item
    ) {
        if ($item->getMiraklOfferId()) {
            return false;
        }

        return $proceed($item);
    }
}
