<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Compare;

class ComparePlugin
{
    /**
     * @param Compare  $subject
     * @param \Closure $proceed
     * @param Item     $targetItem
     * @param Item     $comparedItem
     * @return bool
     */
    public function aroundCompare(
        Compare $subject,
        \Closure $proceed,
        Item $targetItem,
        Item $comparedItem
    ) {
        if ($comparedItem->getMiraklOfferId() !== $targetItem->getMiraklOfferId()) {
            return false;
        }

        return $proceed($targetItem, $comparedItem);
    }
}
