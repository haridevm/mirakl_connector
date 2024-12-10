<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\System\Config\Source;

class Visibility
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return \Magento\Catalog\Model\Product\Visibility::getOptionArray();
    }
}
