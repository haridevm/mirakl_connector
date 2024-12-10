<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Customizer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirakl\Mcm\Helper\Data as McmHelper;

class MiraklImages implements CustomizerInterface
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
    public function customize(Collection $collection): void
    {
        $imageAttributes = $this->mcmHelper->getImagesAttributes();
        $collection->addAttributeToSelect(array_keys($imageAttributes), 'left');
    }
}
