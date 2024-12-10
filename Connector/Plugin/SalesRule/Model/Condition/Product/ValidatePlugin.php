<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\SalesRule\Model\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\Product;

class ValidatePlugin
{
    /**
     * @param Product       $subject
     * @param \Closure      $proceed
     * @param AbstractModel $model
     * @return bool
     */
    public function aroundValidate(
        Product $subject,
        \Closure $proceed,
        AbstractModel $model
    ) {
        if ($model->getMiraklOfferId()) {
            return false;
        }

        return $proceed($model);
    }
}
