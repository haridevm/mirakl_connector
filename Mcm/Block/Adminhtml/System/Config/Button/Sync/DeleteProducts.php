<?php
namespace Mirakl\Mcm\Block\Adminhtml\System\Config\Button\Sync;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;
use Mirakl\Mcm\Helper\Config;

class DeleteProducts extends AbstractButtons
{
    /**
     * @var Config
     */
    protected $mcmConfig;

    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label'       => 'Delete Products',
            'url'         => 'mirakl/sync/mcm_productsDelete',
            'confirm'     => 'Are you sure ? This will remove all deleted Mirakl products from Magento',
            'class'       => 'scalable',
        ],
        [
            'label'   => 'Reset Date',
            'url'     => 'mirakl/reset/productsDelete',
            'confirm' => 'Are you sure ? This will reset the last products delete synchronization date',
            'class'   => 'scalable primary',
        ]
    ];

    /**
     * @param Context                 $context
     * @param Config                  $mcmConfig
     * @param array                   $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Config $mcmConfig,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct(
            $context,
            $data,
            $secureRenderer
        );
        $this->mcmConfig = $mcmConfig;
    }

    /**
     * @return bool
     */
    public function getDisabled()
    {
        $enabled = $this->mcmConfig->isMcmProductsDeleteEnabled() && ($this->mcmConfig->isMcmEnabled() || $this->mcmConfig->isAsyncMcmEnabled());

        return !$enabled;
    }
}