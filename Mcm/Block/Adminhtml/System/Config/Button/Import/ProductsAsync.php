<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Block\Adminhtml\System\Config\Button\Import;

use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;
use Mirakl\Mcm\Helper\Config;

class ProductsAsync extends AbstractButtons
{
    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label'   => 'Import in Magento',
            'url'     => 'mirakl/import/productsasync',
            'confirm' => 'Are you sure? This will update all modified products since the last synchronization.',
            'class'   => 'scalable',
        ],
        [
            'label'   => 'Reset Date',
            'url'     => 'mirakl/reset/productsasync',
            'confirm' => 'Are you sure? This will reset the last synchronization date.',
            'class'   => 'scalable primary',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function getButtonsHtml()
    {
        if (!$this->_scopeConfig->getValue(Config::XML_PATH_ENABLE_ASYNC_MCM)) {
            $this->setDisabled(true);
        }

        return parent::getButtonsHtml();
    }
}
