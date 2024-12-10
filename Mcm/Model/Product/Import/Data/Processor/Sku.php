<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mcm\Model\Product\Import\Data\Generator\GeneratorInterface;

class Sku implements ProcessorInterface
{
    /**
     * @var GeneratorInterface
     */
    private $skuGenerator;

    /**
     * @param GeneratorInterface $skuGenerator
     */
    public function __construct(GeneratorInterface $skuGenerator)
    {
        $this->skuGenerator = $skuGenerator;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        $data['sku'] = (null === $product)
            ? $this->skuGenerator->generate($data) // Product creation
            : $product['sku']; // Product modification
    }
}
