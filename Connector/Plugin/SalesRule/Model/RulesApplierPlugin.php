<?php
declare(strict_types = 1);

namespace Mirakl\Connector\Plugin\SalesRule\Model;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\RulesApplier;

class RulesApplierPlugin
{
    /**
     * @param RulesApplier $subject
     * @param \Closure     $proceed
     * @param AbstractItem $item
     * @param Collection   $rules
     * @param bool         $skipValidation
     * @param string       $couponCode
     * @return array
     */
    public function aroundApplyRules(
        RulesApplier $subject,
        \Closure $proceed,
        $item,
        $rules,
        $skipValidation,
        $couponCode
    ) {
        if ($item->getMiraklOfferId()) {
            return [];
        }

        return $proceed($item, $rules, $skipValidation, $couponCode);
    }
}