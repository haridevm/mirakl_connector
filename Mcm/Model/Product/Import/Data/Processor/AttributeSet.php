<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mci\Helper\Product\Import\Category as CategoryHelper;

class AttributeSet implements ProcessorInterface
{
    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(CategoryHelper $categoryHelper)
    {
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (null === $product) {
            $category = $this->categoryHelper->getCategoryById($data['mirakl_category_id']);
            $data['attribute_set_id'] = $this->categoryHelper->getCategoryAttributeSet($category)->getId();
        } else {
            $data['attribute_set_id'] = $product['attribute_set_id'];
        }
    }
}