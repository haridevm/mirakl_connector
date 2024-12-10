<?php
namespace Mirakl\Event\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Mirakl\Core\Controller\Adminhtml\RawMessagesTrait;
use Mirakl\Event\Helper\Config;
use Mirakl\Event\Model\HistoryClearer;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Psr\Log\LoggerInterface;

class ClearHistory extends AbstractEventAction
{
    use RawMessagesTrait;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context          $context
     * @param ProcessFactory   $processFactory
     * @param ProcessResource  $processResource
     * @param Config           $config
     * @param LoggerInterface  $logger
     */
    public function __construct(
        Context $context,
        ProcessFactory $processFactory,
        ProcessResource $processResource,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->processFactory = $processFactory;
        $this->processResource = $processResource;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Clear Mirakl events history before a given date
     */
    public function execute()
    {
        try {
            $deleteFrom = $this->config->getEventClearHistoryBeforeDate();
            /** @var Process $process */
            $process = $this->processFactory->create()
                        ->setType(Process::TYPE_ADMIN)
                        ->setName('Clear history of events created before configured days count')
                        ->setHelper(HistoryClearer::class)
                        ->setMethod('execute')
                        ->setParams([$deleteFrom]);
            $this->processResource->save($process);
            $this->messageManager->addSuccessMessage(__('Mirakl events history will be cleared in background.'));
            $this->addRawSuccessMessage(__('Click <a href="%1">here</a> to view process output.', $process->getUrl()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while clearing Mirakl events history: %1', $e->getMessage())
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}