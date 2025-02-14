<?php

declare(strict_types=1);

namespace Mirakl\Api\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Mirakl\Api\Model\Log\LoggerManager;

class Download extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Config::api_developer';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ResultRawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LoggerManager
     */
    protected $loggerManager;

    /**
     * @param Action\Context   $context
     * @param FileFactory      $fileFactory
     * @param ResultRawFactory $resultRawFactory
     * @param LoggerManager    $loggerManager
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        ResultRawFactory $resultRawFactory,
        LoggerManager $loggerManager
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->loggerManager = $loggerManager;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $fileName = basename($this->loggerManager->getLogFile());

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $this->loggerManager->getLogFileSize()
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->loggerManager->getLogFileContents());

        return $resultRaw;
    }
}
