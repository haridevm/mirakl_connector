<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Boolean extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        $yes = in_array($this->_getValue($row), ['true', '1', 1, true], true);
        $value = $yes ? 'Yes' : 'No';
        $class = $yes ? 'grid-severity-notice' : 'grid-severity-critical';

        return sprintf('<span class="%s"><span>%s</span></span>', $class, __($value));
    }
}
