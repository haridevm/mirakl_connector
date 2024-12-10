<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Mirakl\Mcm\Helper\Data as McmHelper;

class Identifier implements CleanerInterface
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        $data[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID] = $data[$this->fieldName];
        unset($data[$this->fieldName]);
        unset($data['sku']); // do not erase Magento product SKU
    }
}
