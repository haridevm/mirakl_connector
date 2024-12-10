<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Api;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Mirakl\Process\Helper\Data as ProcessHelper;

class GetSynchronizationErrorReportObserver implements ObserverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProcessHelper
     */
    protected $processHelper;

    /**
     * @param Filesystem    $filesystem
     * @param ProcessHelper $processHelper
     */
    public function __construct(Filesystem $filesystem, ProcessHelper $processHelper)
    {
        $this->filesystem = $filesystem;
        $this->processHelper = $processHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $reportFilePath = $observer->getEvent()->getData('report_file_path');
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $reportFilePath = $this->processHelper->getRelativePath($reportFilePath, $directory);

        try {
            $data = $directory->readFile($reportFilePath);
        } catch (FileSystemException $e) {
            return;
        }

        if ($data && $this->hasReportError(json_decode($data, true))) {
            $hasError = $observer->getEvent()->getData('has_error');
            $hasError->setData('error', true);
        }
    }

    /**
     * Data has error for CM23 Response
     *
     * @param array $report
     * @return bool
     */
    public function hasReportError(array $report)
    {
        if (isset($report['processed_items'])) {
            foreach ($report['processed_items'] as $data) {
                if (isset($data['integration_errors']) || isset($data['synchronization_errors'])) {
                    return true;
                }
            }
        }

        if (isset($report['global_errors'])) {
            return true;
        }

        return false;
    }
}
