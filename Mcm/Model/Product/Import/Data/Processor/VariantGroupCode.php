<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\ResourceModel\Product\RelationHandler;

class VariantGroupCode implements ProcessorInterface
{
    /**
     * @var RelationHandler
     */
    private $relationHandler;

    /**
     * @param RelationHandler $relationHandler
     */
    public function __construct(RelationHandler $relationHandler)
    {
        $this->relationHandler = $relationHandler;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (null !== $product) {
            $oldVGC = $product[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] ?? '';
            $newVGC = $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] ?? '';
            if (strlen($oldVGC) && $newVGC != $oldVGC) {
                // Old VGC has been modified or removed, we need to
                // remove the product relation from the old parent product
                $this->relationHandler->delete([$product['entity_id']]);
            }
        }
    }
}