<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Message;

use Magento\Framework\App\Action\HttpGetActionInterface;

class View extends AbstractMessage implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if (!$thread = $this->getThread()) {
            $this->messageManager->addErrorMessage(__('Thread not found.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setUrl($this->_url->getUrl('marketplace/message/index'));
        }

        $this->registry->register('mirakl_thread', $thread);

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('marketplace/message');
        }

        return $resultPage;
    }
}
