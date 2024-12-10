<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Repository;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class AttributeSet
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Attribute set pairs as [id => name]
     *
     * @var array
     */
    private $attrSets;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    private function load()
    {
        if (null === $this->attrSets) {
            $this->attrSets = [];
            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            foreach ($this->collectionFactory->create() as $attributeSet) {
                $this->attrSets[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
            }
        }
    }

    /**
     * Returns attribute set name for given id
     *
     * @param int $setId
     * @return string|null
     */
    public function get($setId): ?string
    {
        $this->load();

        return $this->attrSets[$setId] ?? null;
    }
}