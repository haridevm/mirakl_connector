<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Decorator;

use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory;
use Mirakl\Mcm\Helper\Data as McmHelper;

class Categories implements DecoratorInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function decorate(array &$rows): void
    {
        $mcmProductIds = array_column($rows, McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID);

        if (!empty($mcmProductIds)) {
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToFilter(McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID, ['in' => $mcmProductIds]);
            $collection->load();
            $collection->addCategoryIds(false);

            foreach ($collection->getItems() as $item) {
                $miraklProductId = $item[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID];
                $rows[$miraklProductId]['categories'] = $item['category_ids'] ?? [];
            }
        }
    }
}
