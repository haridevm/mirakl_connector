<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport\Data\Cleaner;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Data\Cleaner\CleanerInterface;

class Images implements CleanerInterface
{
    /**
     * @var McmHelper
     */
    private $mcmHelper;

    /**
     * @param McmHelper $mcmHelper
     */
    public function __construct(McmHelper $mcmHelper)
    {
        $this->mcmHelper = $mcmHelper;
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        $imageAttributes = $this->mcmHelper->getImagesAttributes();

        foreach ($imageAttributes as $imageAttribute) {
            /** @var EavAttribute $imageAttribute */
            $attrCode = $imageAttribute->getAttributeCode();
            if (!empty($data[$attrCode])) {
                $data[$attrCode] = $data[$attrCode]['source'] ?? null;
            }
        }
    }
}
