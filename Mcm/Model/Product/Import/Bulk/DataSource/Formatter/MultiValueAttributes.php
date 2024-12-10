<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogImportExport\Model\Import;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mci\Model\Product\Attribute\ProductAttributesFinder;
use Mirakl\Mcm\Helper\Config as McmConfig;

class MultiValueAttributes implements FormatterInterface
{
    /**
     * @var ProductAttributesFinder
     */
    private $attributesFinder;

    /**
     * @var McmConfig
     */
    private $mcmConfig;

    /**
     * @param ProductAttributesFinder $attributesFinder
     * @param McmConfig               $mcmConfig
     */
    public function __construct(
        ProductAttributesFinder $attributesFinder,
        McmConfig $mcmConfig
    ) {
        $this->attributesFinder = $attributesFinder;
        $this->mcmConfig = $mcmConfig;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        $attributes = $this->attributesFinder->getAttributesByCode();

        foreach ($attributes as $key => $attribute) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                continue;
            }
            // Identifier attributes are always sent as multi value by Mirakl in JSON format
            // JSON format is used only for async MCM import
            if ($this->isMultiValueField($attribute)
                || $this->isTextField($attribute)
                || in_array($key, $this->mcmConfig->getMcmIdentifiersAttributes())
            ) {
                $data[$key] = implode(Import\Product::PSEUDO_MULTI_LINE_SEPARATOR, $data[$key]);
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @return bool
     */
    private function isMultiValueField(Attribute $attribute): bool
    {
        return $attribute->getFrontendInput() == 'multiselect';
    }

    /**
     * @param Attribute $attribute
     * @return bool
     */
    private function isTextField(Attribute $attribute): bool
    {
        return $attribute->getFrontendInput() == 'text';
    }
}