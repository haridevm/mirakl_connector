<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Helper\Product\Export;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Mirakl\Mcm\Helper\Data as McmHelper;

class Report extends AbstractHelper
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductResourceFactory
     */
    protected $productResourceFactory;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @param Context                  $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductResourceFactory   $productResourceFactory
     */
    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        ProductResourceFactory $productResourceFactory
    ) {
        parent::__construct($context);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productResourceFactory = $productResourceFactory;
        $this->productResource = $productResourceFactory->create();
    }

    /**
     * Updates mirakl_mcm_product_id field according to specified report data.
     * Returns the number of updated products.
     *
     * @param array $report
     * @return int
     */
    public function updateMiraklProductIds(array $report)
    {
        $miraklProductIds = [];
        foreach ($report as $data) {
            if (isset($data['product_sku']) && isset($data['mirakl_product_id'])) {
                $miraklProductIds[$data['product_sku']] = $data['mirakl_product_id'];
            }
        }

        if (empty($miraklProductIds)) {
            return 0;
        }

        $miraklProductIdField = McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID;

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('sku', ['in' => array_keys($miraklProductIds)]);
        $collection->addAttributeToFilter([
            ['attribute' => $miraklProductIdField, 'eq' => ''],
            ['attribute' => $miraklProductIdField, 'null' => true],
        ], null, 'left');

        /** @var Product $product */
        foreach ($collection as $product) {
            $this->saveMiraklProductId($product, $miraklProductIds[$product->getSku()]);
        }

        return $collection->count();
    }

    /**
     * @param Product $product
     * @param string  $miraklProductId
     * @return int
     */
    private function saveMiraklProductId(Product $product, string $miraklProductId)
    {
        $connection = $this->productResource->getConnection();

        return $connection->update(
            $this->productResource->getTable('catalog_product_entity'),
            [McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID => $miraklProductId],
            ['sku = ?' => $product->getSku()]
        );
    }
}
