<?php
namespace Mirakl\Mcm\Block\Adminhtml\System\Config\Button\Sync;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Mirakl\Connector\Block\Adminhtml\System\Config\Button\AbstractButtons;
use Mirakl\Mcm\Helper\Config;

class ExportProducts extends AbstractButtons
{
    /**
     * @var Config
     */
    private $mcmConfig;

    /**
     * @var array
     */
    protected $buttonsConfig = [
        [
            'label'   => 'Export to Mirakl',
            'url'     => 'mirakl/sync/mcm_products',
            'confirm' => 'Are you sure? This will export all MCM products data to Mirakl platform.',
            'class'   => 'scalable',
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
        parent::__construct($context, $data, $secureRenderer);
        $this->mcmConfig = $mcmConfig;
        $enabled = ($this->mcmConfig->isMcmEnabled() || $this->mcmConfig->isAsyncMcmEnabled()) && $this->mcmConfig->isSyncMcmProducts();
        $this->setDisabled(!$enabled);
    }
}