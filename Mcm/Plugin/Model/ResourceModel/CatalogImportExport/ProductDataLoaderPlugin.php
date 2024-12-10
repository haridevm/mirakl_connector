<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Plugin\Model\ResourceModel\CatalogImportExport;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogImportExport\Model\ResourceModel\ProductDataLoader;
use Mirakl\Mcm\Model\Product\Import\Bulk\SkuProcessor;

class ProductDataLoaderPlugin
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @param ProductResource $productResource
     * @param SkuProcessor    $skuProcessor
     */
    public function __construct(
        ProductResource $productResource,
        SkuProcessor $skuProcessor
    ) {
        $this->productResource = $productResource;
        $this->skuProcessor = $skuProcessor;
    }

    /**
     * @param ProductDataLoader $subject
     * @param \Closure          $proceed
     * @param array             $columns
     * @return \Generator
     */
    public function aroundGetProductsData(ProductDataLoader $subject, \Closure $proceed, array $columns): \Generator
    {
        $resource = $this->productResource;
        $connection = $resource->getConnection();
        $select = $connection->select()
            ->from($resource->getTable('catalog_product_entity'), $columns);

        $skusFilter = $this->skuProcessor->getSkusFilter();

        if (is_array($skusFilter)) {
            // If $skuFilter is an array, it has been initialized by SkuProcessor
            if (!empty($skusFilter)) {
                // Filter by skus if possible for better performances and lower memory usage (since 2.4.7)
                $select->where('sku IN (?)', $skusFilter);
            } else {
                // If no skus are provided, we should not return any product
                $select->where('1 = 0');
            }
        }

        $stmt = $connection->query($select);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }
}
