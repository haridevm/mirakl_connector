<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Document\Type;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\Document\Type;

class Index extends Type implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Document Type List'), __('Document Type'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Document Type List'));
        $this->_view->renderLayout();
    }
}
