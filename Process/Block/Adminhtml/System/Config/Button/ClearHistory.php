<?php

declare(strict_types=1);

namespace Mirakl\Process\Block\Adminhtml\System\Config\Button;

use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;

class ClearHistory extends AbstractButtons
{
    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label'       => 'Clear History',
            'url'         => 'mirakl/process/clearHistory',
            'confirm'     => 'Are you sure ? This will clear all process history before configured days',
            'class'       => 'scalable',
        ]
    ];
}
