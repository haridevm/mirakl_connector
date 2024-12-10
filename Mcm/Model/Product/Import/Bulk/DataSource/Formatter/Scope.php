<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\Store\Api\Data\StoreInterface;

class Scope implements FormatterInterface
{
    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        if (!empty($data['stores']) && $data['product_type'] === 'simple') {
            $data['store_view_code'] = implode(',', $data['stores']);
        }
        if (!empty($data['websites'])) {
            $data['_product_websites'] = implode(',', $data['websites']);
        }
        unset($data['stores'], $data['websites']);
    }
}