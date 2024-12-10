<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Customizer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Type implements CustomizerInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function customize(Collection $collection): void
    {
        $collection->addFieldToFilter('type_id', $this->type);
    }
}