<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shop;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Helper\Data as CoreHelper;

/**
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
abstract class Shop extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Core::shops';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

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
     * @param Registry   $coreRegistry
     * @param Config     $connectorConfig
     * @param CoreHelper $coreHelper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $connectorConfig,
        CoreHelper $coreHelper
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->connectorConfig = $connectorConfig;
        $this->coreHelper = $coreHelper;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->showLastUpdateDate('shops');
        $this->_view->loadLayout();

        $this->_setActiveMenu('Mirakl_Core::shops')
            ->_addBreadcrumb(__('Mirakl'), __('Mirakl'));

        return $this;
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
