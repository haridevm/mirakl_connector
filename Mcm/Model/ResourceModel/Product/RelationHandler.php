<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\ResourceModel\Product;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\Relation;

class RelationHandler
{
    /**
     * @var Configurable
     */
    private $configurableResource;

    /**
     * @var Relation
     */
    private $productRelation;

    /**
     * @param Configurable $configurableResource
     * @param Relation     $productRelation
     */
    public function __construct(
        Configurable $configurableResource,
        Relation $productRelation
    ) {
        $this->configurableResource = $configurableResource;
        $this->productRelation = $productRelation;
    }

    /**
     * @param int[] $childrenIds
     */
    public function delete(array $childrenIds): void
    {
        if (empty($childrenIds)) {
            return;
        }

        $connection = $this->configurableResource->getConnection();

        // Remove in catalog_product_super_link
        $where = ['product_id IN (?)' => $childrenIds];
        $connection->delete($this->configurableResource->getMainTable(), $where);

        // Remove in catalog_product_relation
        $where = ['child_id IN (?)' => $childrenIds];
        $connection->delete($this->productRelation->getMainTable(), $where);
    }
}
