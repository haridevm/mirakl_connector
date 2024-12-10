<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractProcessAction implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $page->setActiveMenu('Mirakl_Process::process');
        $page->getConfig()->getTitle()->prepend(__('Process List'));

        return $page;
    }
}
