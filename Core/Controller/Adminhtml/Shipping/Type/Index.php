<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shipping\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Helper\Data as CoreHelper;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Core::shipping_type';

    /**
     * @var Config
     */
    private $connectorConfig;

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @param Context    $context
     * @param Config     $connectorConfig
     * @param CoreHelper $coreHelper
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
     * @return $this
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
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
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Shipping Method List'), __('Shipping Method List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Method List'));
        $this->_view->renderLayout();
    }
}
