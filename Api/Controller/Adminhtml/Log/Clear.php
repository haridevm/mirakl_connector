<?php

declare(strict_types=1);

namespace Mirakl\Api\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Mirakl\Api\Model\Log\LoggerManager;

class Clear extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Config::api_developer';

    /**
     * @var LoggerManager
     */
    protected $loggerManager;

    /**
     * @param Action\Context $context
     * @param LoggerManager  $loggerManager
     */
    public function __construct(
        Action\Context $context,
        LoggerManager $loggerManager
    ) {
        parent::__construct($context);
        $this->loggerManager = $loggerManager;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('adminhtml/system_config/edit/section/mirakl_api_developer'));

        $this->loggerManager->clear();
        $this->messageManager->addSuccessMessage(__('Log file has been cleared.'));

        return $resultRedirect;
    }
}
