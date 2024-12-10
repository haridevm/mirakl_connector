<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Builder\Configurable;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Data;
use Mirakl\Mcm\Model\Product\Import\Repository;

class Builder implements BuilderInterface, Data\Builder\LoaderInterface
{
    /**
     * @var Data\Processor
     */
    private $dataProcessor;

    /**
     * @var Repository\Product
     */
    private $productRepository;

    /**
     * @param Data\Processor     $dataProcessor
     * @param Repository\Product $productRepository
     */
    public function __construct(
        Data\Processor $dataProcessor,
        Repository\Product $productRepository
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function load(array $values = []): array
    {
        $this->productRepository->load($values, McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE);

        return $this->productRepository->getList();
    }

    /**
     * @inheritdoc
     */
    public function build(string $vgc, array $children): array
    {
        $product = $this->productRepository->get($vgc);

        // Default behavior is to take the first child to create the configurable product
        $data = (null === $product) ? current($children) : $product;

        $data['product_type'] = Configurable::TYPE_CODE;

        $data['variations'] = [];
        foreach ($children as $child) {
            $data['variations'][] = $child['variations'];
        }

        $this->dataProcessor->process($data, $product);

        return $data;
    }
}