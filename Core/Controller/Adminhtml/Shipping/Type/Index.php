<?php
namespace Mirakl\Core\Controller\Adminhtml\Shipping\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Helper\Data as CoreHelper;

class Index extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mirakl_Core::shipping_type';

    /**
     * @var Config
     */
    private $connectorConfig;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @param   Context     $context
     * @param   Config      $connectorConfig
     * @param   CoreHelper  $coreHelper
     */
    public function __construct(
        Context $context,
        Config $connectorConfig,
        CoreHelper $coreHelper
    ) {
        parent::__construct($context);
        $this->connectorConfig = $connectorConfig;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Init action
     *
     * @return  $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mirakl_Core::shipping_types');
        $this->_addBreadcrumb(__('Mirakl'), __('Mirakl'));
        $lastSyncDate = $this->connectorConfig->getSyncDate('shipping_type');
        if ($lastSyncDate) {
            $this->messageManager->addNoticeMessage(
                __('Last synchronization: %1', $this->coreHelper->formatDateTime($lastSyncDate))
            );
        }

        return $this;
    }

    /**
     * @return  void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Shipping Method List'), __('Shipping Method List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Method List'));
        $this->_view->renderLayout();
    }
}
