<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class SkuProcessor extends \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductResourceFactory
     */
    private $productResourceFactory;

    /**
     * Used to filter existing products query.
     * It is not necessary to fetch all the products
     * in database for an import that only contains
     * couple of products.
     * @see _getSkus()
     *
     * @var array|null
     */
    private $skusFilter;

    /**
     * @param ProductFactory         $productFactory
     * @param MetadataPool           $metadataPool
     * @param ProductResourceFactory $productResourceFactory
     */
    public function __construct(
        ProductFactory $productFactory,
        MetadataPool $metadataPool,
        ProductResourceFactory $productResourceFactory
    ) {
        parent::__construct($productFactory);
        $this->metadataPool = $metadataPool;
        $this->productResourceFactory = $productResourceFactory;
    }

    /**
     * @return array|null
     */
    public function getSkusFilter(): ?array
    {
        return $this->skusFilter;
    }

    /**
     * @param array $skus
     */
    public function setSkusFilter(array $skus)
    {
        $this->skusFilter = $skus;
    }

    /**
     * Need to override the default method to filter by existing skus
     * for better perfomances and lower memory usage.
     *
     * @inheritdoc
     */
    protected function _getSkus(): array
    {
        $oldSkus = [];
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        $entityLinkField = $this->getProductEntityLinkField();

        if ($entityLinkField != $this->getProductIdentifierField()) {
            $columns[] = $entityLinkField;
        }

        foreach ($this->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = strtolower($info['sku']);
            $oldSkus[$sku] = [
                'type_id'        => $typeId,
                'attr_set_id'    => $info['attribute_set_id'],
                'entity_id'      => $info['entity_id'],
                'supported_type' => isset($this->productTypeModels[$typeId]),
                $entityLinkField => $info[$entityLinkField],
            ];
        }

        return $oldSkus;
    }

    /**
     * @param array $columns
     * @return array
     */
    private function getProductEntitiesInfo(array $columns): array
    {
        if (is_array($this->skusFilter) && empty($this->skusFilter)) {
            return []; // SKUs filter has been set but is empty, return an empty list of product
        }

        $resource = $this->productResourceFactory->create();
        $connection = $resource->getConnection();
        $select = $connection->select()->from($resource->getEntityTable(), $columns);

        if (!empty($this->skusFilter)) {
            $select->where('sku IN (?)', $this->skusFilter);
        }

        return $connection->fetchAll($select);
    }

    /**
     * @return EntityMetadataInterface
     */
    private function getMetadata(): EntityMetadataInterface
    {
        return $this->metadataPool->getMetadata(ProductInterface::class);
    }

    /**
     * @return string
     */
    private function getProductEntityLinkField(): string
    {
        return $this->getMetadata()->getLinkField();
    }

    /**
     * @return string
     */
    private function getProductIdentifierField(): string
    {
        return $this->getMetadata()->getIdentifierField();
    }
}
