<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Mirakl\Process\Helper\Data;
use Mirakl\Process\Helper\Config;
use Mirakl\Process\Model\TimeoutManager;

class Async extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Process::process';

    /**
     * @var TimeoutManager
     */
    private $timeoutManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context        $context
     * @param TimeoutManager $timeoutManager
     * @param Data           $helper
     * @param config         $config
     */
    public function __construct(
        Context $context,
        TimeoutManager $timeoutManager,
        Data $helper,
        Config $config
    ) {
        parent::__construct($context);
        $this->timeoutManager = $timeoutManager;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        ob_start();

        $body = [];
        $process = null;

        if ($this->config->isAutoAsyncExecution()) {
            // Apply timeout before executing process
            try {
                $timeoutProcesses = $this->timeoutManager->applyTimeout();
                $updated = $timeoutProcesses->count();
                $body[] = __('%1 process%2 in timeout', $updated, $updated > 1 ? 'es' : '');
            } catch (\Exception $e) {
                $body[] = $e->getMessage();
            }
            $process = $this->helper->getPendingProcess();
            $body[] = $process ? __('Processing #%1', $process->getId()) : __('Nothing to process in background');
        } else {
            $body[] = __('Automatic process execution is disabled');
        }

        $this->getResponse()
            ->setBody(implode(' / ', $body))
            ->sendResponse();

        session_write_close();
        ob_end_flush();
        flush();

        if ($process) {
            $process->run();
        }

        // Nothing more to do
        exit;  // phpcs:ignore
    }
}
