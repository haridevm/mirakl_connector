<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Conditions extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        /** @var \Mirakl\Core\Model\Shipping\Zone $row */
        if ($row->getRule()->getConditions()->getConditions()) {
            return nl2br($row->getRule()->getConditions()->asStringRecursive());
        }

        return __('No condition');
    }
}
