<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Adapter;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Mirakl\Core\Model\File\FieldCollector\CollectorInterface as FieldCollectorInterface;
use Mirakl\Core\Model\Stack\StackInterface;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;
use Mirakl\Mcm\Model\Product\Import\Bulk\SkuProcessor;
use Mirakl\Mcm\Model\Product\Import\Data\Builder\Simple\BuilderInterface as SimpleBuilderInterface;
use Mirakl\Mcm\Model\Product\Import\Data\Builder\Configurable\BuilderInterface as ConfigurableBuilderInterface;

class Bulk extends AbstractAdapter
{
    /**
     * @var DataSource\ImporterInterface
     */
    private $importer;

    /**
     * @var DataSource\ManagerInterface
     */
    private $dataSourceManager;

    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @var FieldCollectorInterface
     */
    private $fieldCollector;

    /**
     * @var StackInterface
     */
    private $parentProducts;

    /**
     * @var SimpleBuilderInterface
     */
    private $simpleBuilder;

    /**
     * @var ConfigurableBuilderInterface
     */
    private $configurableBuilder;

    /**
     * @var string
     */
    private $miraklProductIdField;

    /**
     * @var string
     */
    private $miraklProductSkuField;

    /**
     * @var string
     */
    private $miraklVariantGroupCodeField;

    /**
     * @param DataSource\ImporterInterface $importer
     * @param DataSource\ManagerInterface  $dataSourceManager
     * @param SkuProcessor                 $skuProcessor
     * @param FieldCollectorInterface      $fieldCollector
     * @param StackInterface               $parentProducts
     * @param SimpleBuilderInterface       $simpleBuilder
     * @param ConfigurableBuilderInterface $configurableBuilder
     * @param string                       $miraklProductIdField
     * @param string                       $miraklProductSkuField
     * @param string                       $miraklVariantGroupCodeField
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        DataSource\ImporterInterface $importer,
        DataSource\ManagerInterface $dataSourceManager,
        SkuProcessor $skuProcessor,
        FieldCollectorInterface $fieldCollector,
        StackInterface $parentProducts,
        SimpleBuilderInterface $simpleBuilder,
        ConfigurableBuilderInterface $configurableBuilder,
        string $miraklProductIdField,
        string $miraklProductSkuField,
        string $miraklVariantGroupCodeField
    ) {
        $this->importer = $importer;
        $this->dataSourceManager = $dataSourceManager;
        $this->skuProcessor = $skuProcessor;
        $this->fieldCollector = $fieldCollector;
        $this->parentProducts = $parentProducts;
        $this->simpleBuilder = $simpleBuilder;
        $this->configurableBuilder = $configurableBuilder;
        $this->miraklProductIdField = $miraklProductIdField;
        $this->miraklProductSkuField = $miraklProductSkuField;
        $this->miraklVariantGroupCodeField = $miraklVariantGroupCodeField;
    }

    /**
     * @inheritdoc
     */
    public function before()
    {
        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        // Collect mcm products ids and skus for deduplication
        $deduplicationValues = $this->collect([
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID  => $this->miraklProductIdField,
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_SKU => $this->miraklProductSkuField
        ]);

        $products = $this->simpleBuilder->load($deduplicationValues);

        // Collect parent products VGC or SKU if the parent SKU has been
        // used as VGC during CM21 export for deduplication
        $configurableDeduplicationValues = $this->collect([
            McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE => $this->miraklVariantGroupCodeField,
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_SKU        => $this->miraklVariantGroupCodeField,
        ]);

        $parents = $this->configurableBuilder->load($configurableDeduplicationValues);

        $skus = array_merge(
            array_column($products, 'sku'),
            array_column($parents, 'sku')
        );

        $this->skuProcessor->setSkusFilter($skus);
    }

    /**
     * Collects all values of specific fields from the current import file
     *
     * @param array $fields
     * @return array
     */
    private function collect(array $fields): array
    {
        return $this->fieldCollector->collect($this->file, $fields);
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        // Build simple product data
        $data = $this->simpleBuilder->build($data);

        // Retrieve the variant group code if present
        $vgc = $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] ?? null;
        if ($vgc) {
            // Need to aggregate variant products so we can create/update the parent product later
            $this->parentProducts->add($vgc, $data);
        }
        $this->dataSourceManager->add($data);

        return $data['sku'];
    }

    /**
     * @inheritdoc
     */
    public function after()
    {
        if ($this->getProcess()->isProcessing()) {
            // Ensure process has not failed before saving products
            $this->prepareParentProducts();
            $this->saveProducts();
        }
    }

    /**
     * Prepare configurable products data before save
     */
    private function prepareParentProducts()
    {
        foreach ($this->parentProducts as $vgc => $children) {
            $parent = $this->configurableBuilder->build((string) $vgc, $children);
            $this->dataSourceManager->add($parent);
        }
    }

    /**
     * Save products in bulk mode
     */
    private function saveProducts()
    {
        $process = $this->getProcess();
        $process->output(__('Sending prepared data to Magento bulk import API ...'), true);

        $dataSource = $this->dataSourceManager->getDataSource();
        $this->importer->import($dataSource);

        $process->hr();
        $process->output(__('Bulk import time: %1s', round($this->importer->getExecutionTime(), 2)));
        $process->output($this->importer->getOutput());

        if ($errors = $this->importer->getErrors()) {
            /** @var ProcessingError $error */
            foreach ($errors as $error) {
                $process->output(__(
                    'ERROR in row %1 (%2): %3',
                    $error->getRowNumber() + 1,
                    $error->getErrorLevel(),
                    $error->getErrorMessage()
                ));
            }
        }
    }
}
