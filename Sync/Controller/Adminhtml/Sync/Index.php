<?php

declare(strict_types=1);

namespace Mirakl\Sync\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Mirakl_Sync::sync';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Mirakl_Sync::sync');
        $page->getConfig()->getTitle()->prepend(__('Synchronization List'));

        return $page;
    }

    /**
     * @param string $errorMessage
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function redirectError($errorMessage)
    {
        $this->messageManager->addErrorMessage($errorMessage);
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
