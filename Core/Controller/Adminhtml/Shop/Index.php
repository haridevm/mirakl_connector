<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Shop;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends Shop implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Shop List'), __('Shop List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shop List'));
        $this->_view->renderLayout();
    }
}
