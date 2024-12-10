<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mci\Helper\Data as MciHelper;

class VariantAttributes implements FormatterInterface
{
    /**
     * @var MciHelper
     */
    private $mciHelper;

    /**
     * @param MciHelper $mciHelper
     */
    public function __construct(MciHelper $mciHelper)
    {
        $this->mciHelper = $mciHelper;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data, ?StoreInterface $store = null): void
    {
        if ($data['product_type'] !== Configurable::TYPE_CODE) {
            return;
        }

        foreach (array_keys($this->mciHelper->getVariantAttributes()) as $attrCode) {
            unset($data[$attrCode]);
        }
    }
}
