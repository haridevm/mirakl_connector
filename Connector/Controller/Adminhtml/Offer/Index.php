<?php

declare(strict_types=1);

namespace Mirakl\Connector\Controller\Adminhtml\Offer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Helper\Data as CoreHelper;

/**
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Connector::offers';

    /**
     * @var Config
     */
    protected $connectorConfig;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

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
     */
    protected function _initAction()
    {
        if ($this->isOffersImportDisabled()) {
            $this->messageManager->addErrorMessage(
                __('Offers import is disabled. Go to Mirakl > Configuration > Synchronization to enable it.')
            );
        } else {
            $entity = $this->connectorConfig->isOffersImportAsyncEnabled() ? 'offers_async' : 'offers';
            $this->showLastUpdateDate($entity);
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Mirakl_Connector::offers')
            ->_addBreadcrumb(__('Mirakl'), __('Mirakl'));

        return $this;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Offer List'), __('Offer List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Offer List'));
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    private function isOffersImportDisabled()
    {
        return !$this->connectorConfig->isOffersImportEnabled()
            && !$this->connectorConfig->isOffersImportAsyncEnabled();
    }

    /**
     * Adds a notice that displays last synchronization date of specified entity
     *
     * @param string $entity
     */
    protected function showLastUpdateDate($entity)
    {
        if ($lastUpdateDate = $this->connectorConfig->getSyncDate($entity)) {
            $this->messageManager->addSuccessMessage(
                __('Last synchronization: %1', $this->coreHelper->formatDateTime($lastUpdateDate))
            );
        }
    }
}
