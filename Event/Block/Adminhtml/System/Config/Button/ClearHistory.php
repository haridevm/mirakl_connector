<?php
namespace Mirakl\Event\Block\Adminhtml\System\Config\Button;

use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;

class ClearHistory extends AbstractButtons
{
    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label'       => 'Clear History',
            'url'         => 'mirakl/event/clearHistory',
            'confirm'     => 'Are you sure ? This will clear all Mirakl events history before configured days.',
            'class'       => 'scalable',
        ]
    ];
}