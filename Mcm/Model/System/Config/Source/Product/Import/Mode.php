<?php
namespace Mirakl\Mcm\Model\System\Config\Source\Product\Import;

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'bulk', 'label' => __('Bulk')],
            ['value' => 'standard', 'label' => __('Sequential (legacy)')],
        ];
    }
}
