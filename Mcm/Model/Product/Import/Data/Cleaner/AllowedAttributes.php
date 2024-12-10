<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

use Mirakl\Mci\Model\Product\Attribute\ProductAttributesFinder;

class AllowedAttributes implements CleanerInterface
{
    /**
     * @var ProductAttributesFinder
     */
    private $attributesFinder;

    /**
     * @var array
     */
    private $allowedAttributes;

    /**
     * @var string[]
     */
    private $extraColumns;

    /**
     * @param ProductAttributesFinder $attributesFinder
     * @param array $extraColumns
     */
    public function __construct(ProductAttributesFinder $attributesFinder, array $extraColumns = [])
    {
        $this->attributesFinder = $attributesFinder;
        $this->extraColumns = $extraColumns;
    }

    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        $data = array_intersect_key($data, array_flip($this->getAllowedAttributes()));
    }

    /**
     * @return string[]
     */
    private function getAllowedAttributes(): array
    {
        if (null === $this->allowedAttributes) {
            $allowedAttributes = array_keys($this->attributesFinder->getAttributesByCode());
            $this->allowedAttributes = array_merge($allowedAttributes, $this->extraColumns);
        }

        return $this->allowedAttributes;
    }
}