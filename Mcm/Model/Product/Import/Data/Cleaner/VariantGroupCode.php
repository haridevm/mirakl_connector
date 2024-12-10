<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Mirakl\Mcm\Helper\Data as McmHelper;

class VariantGroupCode implements CleanerInterface
{
    /**
     * @var McmHelper
     */
    private $mcmHelper;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param McmHelper $mcmHelper
     * @param string    $fieldName
     */
    public function __construct(
        McmHelper $mcmHelper,
        string $fieldName
    ) {
        $this->mcmHelper = $mcmHelper;
        $this->fieldName = $fieldName;
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        if (isset($data[$this->fieldName])) {
            $variantId = $data[$this->fieldName];
            $variantId = $this->mcmHelper->cleanVariantId($variantId);
            $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] = $variantId;
            unset($data[$this->fieldName]);
        }
    }
}
