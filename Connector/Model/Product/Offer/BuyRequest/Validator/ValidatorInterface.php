<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\BuyRequest\Validator;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Mirakl\Connector\Model\Offer;

interface ValidatorInterface
{
    /**
     * @param DataObject $buyRequest
     * @param Product    $product
     * @param Offer      $offer
     * @return bool
     */
    public function validate(DataObject $buyRequest, Product $product, Offer $offer): bool;
}
