<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mcm\Helper\Data as McmHelper;

class MiraklFields implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        if (null === $product) {
            $data['mirakl_sync'] = 1;
            $data[McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER] = false;
        }
    }
}