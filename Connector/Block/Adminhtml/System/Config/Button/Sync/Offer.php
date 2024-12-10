<?php

declare(strict_types=1);

namespace Mirakl\Connector\Block\Adminhtml\System\Config\Button\Sync;

use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;

class Offer extends AbstractButtons
{
    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label' => 'Import in Magento',
            'url' => 'mirakl/sync/offer',
            'confirm' => 'Are you sure? This will update all modified offers since the last synchronization.',
            'class' => 'scalable',
            'config_path' => \Mirakl\Connector\Helper\Config::XML_PATH_OFFERS_IMPORT_ENABLE,
        ],
        [
            'label' => 'Reset Date',
            'url' => 'mirakl/reset/offer',
            'confirm' => 'Are you sure? This will reset the last synchronization date.',
            'class' => 'scalable primary',
            'config_path' => \Mirakl\Connector\Helper\Config::XML_PATH_OFFERS_IMPORT_ENABLE,
        ],
    ];
}
