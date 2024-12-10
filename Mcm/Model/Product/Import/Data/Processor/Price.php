<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

class Price implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (null === $product) {
            $data['price'] = 0;
        }
    }
}