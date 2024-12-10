<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Offer\BuyRequest;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

interface ProcessorInterface
{
    /**
     * @param DataObject $buyRequest
     * @param Product    $product
     * @return void
     */
    public function process(DataObject $buyRequest, Product $product): void;
}
