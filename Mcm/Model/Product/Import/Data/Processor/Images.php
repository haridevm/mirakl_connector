<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Mirakl\Mci\Helper\Product\Image as ImageHelper;
use Mirakl\Mcm\Helper\Data as McmHelper;

class Images implements ProcessorInterface
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
    public function process(array &$data, ?array $product = null): void
    {
        $productExists = (null !== $product);
        $imageAttributes = $this->mcmHelper->getImagesAttributes();

        foreach ($imageAttributes as $imageAttribute) {
            /** @var EavAttribute $imageAttribute */
            $attrCode = $imageAttribute->getAttributeCode();
            $productImageValue = $product[$attrCode] ?? null;

            if (!empty($data[$attrCode])) {
                if (!$productExists || $productImageValue != $data[$attrCode]) {
                    $data[McmHelper::ATTRIBUTE_IMAGES_STATUS] = ImageHelper::IMAGES_IMPORT_STATUS_PENDING;
                } elseif (!$imageAttribute->getIsRequired()) {
                    unset($data[$attrCode]);
                }
            } elseif ($productExists && $productImageValue) {
                $data[$attrCode] = ImageHelper::DELETED_IMAGE_URL;
                $data[McmHelper::ATTRIBUTE_IMAGES_STATUS] = ImageHelper::IMAGES_IMPORT_STATUS_PENDING;
            }
        }
    }
}
