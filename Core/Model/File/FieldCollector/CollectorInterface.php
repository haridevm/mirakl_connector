<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\File\FieldCollector;

use Magento\Framework\Filesystem\File\ReadInterface;

interface CollectorInterface
{
    /**
     * Collects fields values from a file (CSV, JSON, ...)
     * $fields param must contain fields in a [$fieldCode => $field, ...] format
     *
     * $field: will be used as the target key to fetch values from the file
     * $fieldCode: will be used as the value code in result array
     *
     * Example:
     *
     * CSV file:
     *
     *   product-sku;ean;category;mirakl-mcm-product-id;description
     *   ef54h7fzr;900046648112;Bags;;Sample bag description
     *   rp54h7fkl;700036648801;Shoes;fe534h654-65hmze7f-546hmrc-56450hj;Sample shoe description
     *   ;600226648841;Watches;klm4h657-66hmze4g-546hmrc-3537hjl7;Sample whatch description
     *
     * $fields: [
     *            'sku' => 'product-sku',
     *            'mcm_product_id' => 'mirakl-mcm-product-id'
     *          ]
     *
     * $result: [
     *            ['sku' => 'ef54h7fzr', 'mcm_product_id' => null],
     *            ['sku' => 'rp54h7fkl', 'mcm_product_id' => 'fe534h654-65hmze7f-546hmrc-56450hj'],
     *            ['sku' => null, 'mcm_product_id' => 'klm4h657-66hmze4g-546hmrc-3537hjl7']
     *          ]
     *
     * @param ReadInterface $file
     * @param array         $fields
     * @return array
     */
    public function collect(ReadInterface $file, array $fields): array;
}
