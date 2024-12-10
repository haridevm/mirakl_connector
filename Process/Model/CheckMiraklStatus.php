<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Mirakl\Api\Helper\SynchroResultInterface;
use Mirakl\Process\Helper\Data as ProcessHelper;

class CheckMiraklStatus
{
    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param ProcessHelper         $processHelper
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        ProcessHelper $processHelper,
        EventManagerInterface $eventManager
    ) {
        $this->processHelper = $processHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Process $process
     * @return void
     */
    public function execute(Process $process): void
    {
        $process->output('Checking Mirakl report status...');

        try {
            $this->check($process);
        } catch (\Exception $e) {
            $process->output('Check report in Mirakl failed: ' . $e->getMessage());
            $process->setMiraklStatus(Process::STATUS_ERROR);
        }

        $process->output('Done!', true);
    }

    /**
     * @param Process $process
     * @return void
     */
    private function check(Process $process): void
    {
        $synchroId = $process->getSynchroId();

        if (empty($synchroId)) {
            $process->output('No synchro id found for current process', true);
            return;
        }

        $process->setMiraklStatus(Process::STATUS_PROCESSING);
        $process->output(sprintf('API Synchro Id: #%s', $synchroId), true);

        $helper = $process->getHelperInstance();

        if (!$helper instanceof SynchroResultInterface) {
            $process->output('Helper does not implement SynchroResultInterface', true);
            return;
        }

        // Check if complete
        $synchroResult = $helper->getSynchroResult($synchroId);

        // Not finished yet
        if ($synchroResult->getStatus() != 'COMPLETE') {
            $process->setMiraklStatus(Process::STATUS_PENDING);

            $process->output('API call is not finished ... try again later', true);
            return;
        }

        if ($synchroResult->getData('has_report')) {
            $reportFile = $helper->getErrorReport($synchroId);
            $hasError = new DataObject(['error' => false]);
            if ($filepath = $this->processHelper->saveFile($reportFile, 'json')) {
                $process->setMiraklFile($filepath);
                // Send an event to check if there is an error in report file
                $this->eventManager->dispatch('mirakl_api_get_synchronization_report', [
                    'report_file_path' => $filepath,
                    'has_error' => $hasError
                ]);
            }

            if ($hasError->getData('error') === true) {
                $process->output('Status ERROR');
                $process->setMiraklStatus(Process::STATUS_ERROR);
            } else {
                $process->output('Status COMPLETED');
                $process->setMiraklStatus(Process::STATUS_COMPLETED);
            }
        } elseif ($synchroResult->getErrorReport()) {
            $reportFile = $helper->getErrorReport($synchroId);
            $process->output('Status ERROR');

            if ($filepath = $this->processHelper->saveFile($reportFile)) {
                $fileSize = $this->processHelper->formatSize($this->processHelper->getFileSize($filepath));
                $process->setMiraklFile($filepath);
                $process->output(__('Error file has been saved as "%1" (%2)', basename($filepath), $fileSize));
            }

            $process->setMiraklStatus(Process::STATUS_ERROR);
        } else {
            $process->output('Status SUCCESS');
            $process->setMiraklStatus(Process::STATUS_COMPLETED);
        }
    }
}