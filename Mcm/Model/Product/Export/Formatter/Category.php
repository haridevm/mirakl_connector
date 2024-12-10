<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mci\Helper\Data as MciHelper;
use Mirakl\Mcm\Helper\Product\Export\Category as CategoryHelper;

class Category implements FormatterInterface
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
    public function format(array &$data): void
    {
        if (!empty($data['mirakl_category_id'])) {
            $data[MciHelper::ATTRIBUTE_CATEGORY] = (string) $data['mirakl_category_id'];
        } elseif (!empty($data['category_paths'])) {
            $data[MciHelper::ATTRIBUTE_CATEGORY] = (string) $this->categoryHelper->getCategoryIdFromPaths($data['category_paths']);
        }
    }
}
