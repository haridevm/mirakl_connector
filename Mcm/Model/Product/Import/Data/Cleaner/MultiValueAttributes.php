<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Mirakl\Mci\Model\Product\Attribute\ProductAttributesFinder;
use Mirakl\Mcm\Model\Product\Import\Data\Parser\ParserInterface;

class MultiValueAttributes implements CleanerInterface
{
    /**
     * @var ProductAttributesFinder
     */
    private $attributesFinder;

    /**
     * @var ParserInterface
     */
    private $multiValueParser;

    /**
     * @param ProductAttributesFinder $attributesFinder
     * @param ParserInterface         $multiValueParser
     */
    public function __construct(ProductAttributesFinder $attributesFinder, ParserInterface $multiValueParser)
    {
        $this->attributesFinder = $attributesFinder;
        $this->multiValueParser = $multiValueParser;
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        $attributes = $this->attributesFinder->getAttributesByCode();
        foreach ($attributes as $key => $attribute) {
            if (!empty($data[$key]) && $this->isMultiValue($attribute)) {
                $data[$key] = $this->multiValueParser->parse($data[$key]);
            }
        }
    }

    /**
     * @param Attribute $attribute
     * @return bool
     */
    private function isMultiValue(Attribute $attribute): bool
    {
        return $attribute->getFrontendInput() == 'multiselect';
    }
}
