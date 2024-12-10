<?php

declare(strict_types=1);

namespace Mirakl\Event\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions = $this->getColumn()->getActions();

        $out = [];
        foreach ($actions as $key => $action) {
            if ($key === 'show_data' && !$row->getData('csv_data')) {
                continue;
            }
            if (is_array($action)) {
                $out[] = $this->_toLinkHtml($action, $row);
            }
        }

        return implode('', $out);
    }
}
