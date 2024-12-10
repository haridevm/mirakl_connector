<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Product\Attribute;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mci\Helper\Config;
use Mirakl\Mci\Helper\Data as MciHelper;

class CategoryRepository
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $categories;

    /**
     * @var array
     */
    private $children;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Config                    $config
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        Config $config
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->config = $config;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function get($categoryId): array
    {
        $this->load();

        return $this->categories[$categoryId] ?? [];
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        $this->load();

        return $this->categories;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getChildren($categoryId): array
    {
        $this->load();

        return $this->children[$categoryId] ?? [];
    }

    /**
     * @return array
     */
    public function getRootCategory(): array
    {
        $this->load();

        return $this->categories[$this->getRootCategoryId()] ?? [];
    }

    /**
     * @return int
     */
    private function getRootCategoryId()
    {
        return $this->config->getHierarchyRootCategoryId();
    }

    /**
     * @return StoreInterface
     */
    private function getStore()
    {
        return $this->config->getCatalogIntegrationStore();
    }

    /**
     * @return void
     */
    private function load()
    {
        if (null !== $this->categories) {
            return;
        }

        $store = $this->getStore();

        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($store->getId())
            ->addAttributeToSelect('name', 'left')
            ->addAttributeToSelect(MciHelper::ATTRIBUTE_ATTR_SET, 'left')
            ->setOrder('position', \Magento\Framework\DB\Select::SQL_ASC);

        $this->categories = [];
        $this->children = [];

        $select = $collection->getSelect();

        foreach ($collection->getConnection()->fetchAll($select) as $row) {
            $this->categories[$row['entity_id']] = $row;
            if (empty($row['parent_id'])) {
                continue;
            }
            if (!isset($this->children[$row['parent_id']])) {
                $this->children[$row['parent_id']] = [];
            }
            $this->children[$row['parent_id']][] = $row;
        }
    }
}
