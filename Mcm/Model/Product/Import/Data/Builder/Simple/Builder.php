<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Builder\Simple;

use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Data;
use Mirakl\Mcm\Model\Product\Import\Repository;

class Builder implements BuilderInterface, Data\Builder\LoaderInterface
{
    /**
     * @var Data\Cleaner
     */
    private $dataCleaner;

    /**
     * @var Data\Processor
     */
    private $dataProcessor;

    /**
     * @var Repository\Product
     */
    private $productRepository;

    /**
     * @param Data\Cleaner $dataCleaner
     * @param Data\Processor $dataProcessor
     * @param Repository\Product $productRepository
     */
    public function __construct(
        Data\Cleaner $dataCleaner,
        Data\Processor $dataProcessor,
        Repository\Product $productRepository
    ) {
        $this->dataCleaner = $dataCleaner;
        $this->dataProcessor = $dataProcessor;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function load(array $values = []): array
    {
        return $this->productRepository->load($values, McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function build(array $data): array
    {
        $this->dataCleaner->clean($data);

        $miraklProductId = $data[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID];
        $product = $this->productRepository->get($miraklProductId);

        $this->dataProcessor->process($data, $product);

        return $data;
    }
}