<?php
namespace Mirakl\Connector\Block\Adminhtml\Offer\Grid;

use Magento\Backend\Block\Widget\Context;
use Mirakl\Connector\Helper\Config;

class Container extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config  $config
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mirakl_Connector';
        $this->_controller = 'adminhtml_offer';
        $this->_headerText = __('Offer List');

        parent::_construct();

        $this->removeButton('add');
        $this->addButton(
            'synchronize',
            [
                'label' => __('Synchronize Offers'),
                'class' => 'save primary',
                'onclick' => 'confirmSetLocation(\'' . __(
                    'Are you sure? This will update all modified offers since the last synchronization.'
                    ) . '\', \'' . $this->getSyncOffersUrl() . '\')'
            ]
        );
    }

    /**
     * @return string
     */
    private function getSyncOffersUrl()
    {
        if ($this->config->isOffersImportAsyncEnabled()) {
            return $this->getUrl('*/sync/offerAsync');
        }

        return $this->getUrl('*/sync/offer');
    }
}
