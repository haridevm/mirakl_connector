<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Catalog\Model\Product;
use Mirakl\Mcm\Model\Product\Import\Data\Collector\CollectorInterface;

class Variations implements ProcessorInterface
{
    /**
     * @var CollectorInterface
     */
    private $variantCollector;

    /**
     * @param CollectorInterface $variantCollector
     */
    public function __construct(CollectorInterface $variantCollector)
    {
        $this->variantCollector = $variantCollector;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if ($data['product_type'] === Product\Type::TYPE_SIMPLE) {
            // Build variations array for configurable product
            $variations = $this->variantCollector->collect($data);
            $variations['sku'] = $data['sku'];
            $data['variations'] = $variations;
        }
    }
}
