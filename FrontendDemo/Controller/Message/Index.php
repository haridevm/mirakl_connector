<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Message;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends AbstractMessage implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->_objectManager->get(\Magento\Customer\Model\Session::class)->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('no-route');

            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Messages'));

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        return $resultPage;
    }
}
