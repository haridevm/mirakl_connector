<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Magento\Framework\Exception\NotFoundException;
use Mirakl\Mci\Helper\Data as MciHelper;

class Category implements CleanerInterface
{
    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        // Retrieve associated category and throw exception if not found
        if (!isset($data[MciHelper::ATTRIBUTE_CATEGORY])) {
            throw new NotFoundException(__('Could not find "category" field in product data'));
        }

        $data['mirakl_category_id'] = $data[MciHelper::ATTRIBUTE_CATEGORY];
        unset($data[MciHelper::ATTRIBUTE_CATEGORY]);
    }
}
