<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

class Categories implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (isset($data['mirakl_category_id'])) {
            $categories = $product['categories'] ?? [];
            $categories[] = (int) $data['mirakl_category_id'];
            $data['categories'] = array_unique($categories);
        }
    }
}