<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * @deprecated Not used anymore
 */
class Html extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        return html_entity_decode($this->_getValue($row));
    }
}
