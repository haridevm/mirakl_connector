<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shipping\Zone;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\Shipping\Zone;

class Index extends Zone implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Shipping Zone List'), __('Shipping Zone List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Zone List'));
        $this->_view->renderLayout();
    }
}
