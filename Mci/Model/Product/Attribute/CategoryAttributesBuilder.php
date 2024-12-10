<?php
namespace Mirakl\Mci\Model\Product\Attribute;

use Magento\Catalog\Model\Category;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mci\Helper\Config as MciConfig;
use Mirakl\Mci\Helper\Data as MciHelper;

class CategoryAttributesBuilder extends \ArrayObject
{
    /**
     * @var MciConfig
     */
    protected $mciConfig;

    /**
     * @var ProductAttributesFinder
     */
    protected $productAttributesFinder;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @param MciConfig               $mciConfig
     * @param ProductAttributesFinder $productAttributesFinder
     * @param CategoryRepository      $categoryRepository
     * @param AttributeRepository     $attributeRepository
     */
    public function __construct(
        MciConfig $mciConfig,
        ProductAttributesFinder $productAttributesFinder,
        CategoryRepository $categoryRepository,
        AttributeRepository $attributeRepository
    ) {
        parent::__construct();
        $this->mciConfig = $mciConfig;
        $this->productAttributesFinder = $productAttributesFinder;
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Category|null $category
     * @return $this
     */
    public function build($category = null)
    {
        $tree = $this->getTree($category);
        $this->buildAssoc($tree);
        $this->removeAncestors($tree);

        return $this;
    }

    /**
     * @param array $node
     */
    private function buildAssoc(array $node)
    {
        // Add exportable attributes
        $exportableCodes = [];
        foreach ($node['attributes'] as $attrCode) {
            if ($this->isAttributeExportable($attrCode)) {
                $exportableCodes[] = $attrCode;
            }
        }

        $this->offsetSet($node['code'], $exportableCodes);

        // Check children
        if (array_key_exists('children', $node) && !empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->buildAssoc($child);
            }
        }
    }

    /**
     * @return int
     * @deprecated
     */
    protected function getRootCategoryId()
    {
        return $this->mciConfig->getHierarchyRootCategoryId();
    }

    /**
     * @return StoreInterface
     * @deprecated
     */
    protected function getStore()
    {
        return $this->mciConfig->getCatalogIntegrationStore();
    }

    /**
     * @param array|null $category
     * @return array
     * @throws \Exception
     */
    private function getTree(array $category = null): array
    {
        if (!$category) {
            $code = '';
            $category = $this->categoryRepository->getRootCategory();
        } else {
            $code = $category['entity_id'];
        }

        $setId = $category[MciHelper::ATTRIBUTE_ATTR_SET];

        $tree = [
            'code'       => $code,
            'name'       => $category['name'],
            'attributes' => $this->attributeRepository->getBySetId($setId),
            'children'   => [],
        ];

        $children = $this->categoryRepository->getChildren($category['entity_id']);

        foreach ($children as $child) {
            $tree['children'][] = $this->getTree($child);
        }

        return $tree;
    }

    /**
     * @param string $attrCode
     * @return bool
     */
    protected function isAttributeExportable($attrCode)
    {
        $attribute = $this->productAttributesFinder->findByCode($attrCode);
        $allowedAttributes = $this->productAttributesFinder->getExportableAttributes();

        return $attribute && array_key_exists($attribute->getId(), $allowedAttributes);
    }

    /**
     * @param array $node
     * @param array $parentAttributes
     */
    private function removeAncestors(array $node, array $parentAttributes = [])
    {
        $this->offsetSet($node['code'], array_diff($this->offsetGet($node['code']), $parentAttributes));
        $parentAttributes = array_merge($parentAttributes, $this->offsetGet($node['code']));

        if (array_key_exists('children', $node) && !empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->removeAncestors($child, $parentAttributes);
            }
        }
    }
}