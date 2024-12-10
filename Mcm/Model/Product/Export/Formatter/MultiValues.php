<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Mcm\Helper\Product\Export\Product as ProductHelper;

class MultiValues implements FormatterInterface
{
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @param ProductHelper $productHelper
     */
    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && $this->productHelper->isAttributeMultiSelect($key)) {
                $data[$key] = explode(',', $value);
            }
        }
    }
}
