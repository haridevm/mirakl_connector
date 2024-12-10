<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

interface ProcessorInterface
{
    /**
     * @param array $data
     * @param array|null $product
     */
    public function process(array &$data, ?array $product = null): void;
}