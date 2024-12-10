<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Api\Data\StoreInterface;

class Variations implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        if (!empty($data['variations'])) {
            if ($data['product_type'] === Configurable::TYPE_CODE) {
                $variations = array_map(function ($variation) {
                    return \Mirakl\refs_to_query_param($variation, '=', ',');
                }, $data['variations']);

                $data['configurable_variations'] = implode('|', $variations);
            }
            unset($data['variations']);
        }
    }
}
