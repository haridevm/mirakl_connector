<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data;

use Mirakl\Mcm\Model\Product\Import\Data\Processor\ProcessorInterface;

class Processor
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @param array $data
     * @param array|null $product
     */
    public function process(array &$data, array $product = null): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($data, $product);
        }
    }
}