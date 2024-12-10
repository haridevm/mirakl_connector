<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

/**
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
abstract class Zone extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Core::shipping_zones';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Mirakl_Core::shipping_zones')
            ->_addBreadcrumb(__('Mirakl'), __('Mirakl'));

        return $this;
    }
}
