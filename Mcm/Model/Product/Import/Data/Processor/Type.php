<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Catalog\Model\Product;

class Type implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (empty($data['product_type'])) {
            $data['product_type'] = (null === $product)
                ? Product\Type::TYPE_SIMPLE
                : $product['type_id'];
        }
    }
}