<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Offer\State;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\Offer\State;

class Index extends State implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Offer Condition List'), __('Offer Condition List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Offer Condition List'));
        $this->_view->renderLayout();
    }
}
