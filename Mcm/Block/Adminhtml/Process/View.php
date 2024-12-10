<?php
namespace Mirakl\Mcm\Block\Adminhtml\Process;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Mirakl\Process\Block\Adminhtml\Process\View\AbstractView;
use Mirakl\Process\Model\Repository as ProcessRepository;

class View extends AbstractView
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Context           $context
     * @param ProcessRepository $processRepository
     * @param Filesystem        $filesystem
     * @param array             $data
     */
    public function __construct(
        Context $context,
        ProcessRepository $processRepository,
        Filesystem $filesystem,
        array $data = []
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($context, $processRepository, $data);
    }

    /**
     * Returns parsing errors report (integration_errors, synchronization_errors, global_errors)
     *
     * @param int $limit
     * @return array
     */
    public function getErrors($limit = 100)
    {
        $errors = [];

        $reportFilePath = $this->getProcess()->getMiraklFile();
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        try {
            $reportFile = $directory->readFile($reportFilePath);
        } catch (FileSystemException $e) {
            return $errors;
        }

        if ($reportFile) {
            $report = json_decode($reportFile, true);

            if (isset($report['processed_items'])) {
                foreach ($report['processed_items'] as $item) {
                    if ($limit <= 1) {
                        break;
                    }
                    $identifier = $item['mirakl_product_id'] ?? $item['product_sku'];
                    if (isset($item['integration_errors'])) {
                        foreach ($item['integration_errors'] as $integrationError) {
                            $errors['products'][$identifier][] = ['integration', $integrationError['code'] ?? '', $integrationError['message'] ?? ''];
                            $limit--;
                        }
                    }
                    if (isset($item['synchronization_errors'])) {
                        foreach ($item['synchronization_errors'] as $synchronizationError) {
                            $errors['products'][$identifier][] = ['synchronization', $synchronizationError['code'] ?? '', $synchronizationError['message'] ?? ''];
                            $limit--;
                        }
                    }
                }
            }

            if (isset($report['global_errors'])) {
                foreach ($report['global_errors'] as $globalError) {
                    if ($limit <= 1) {
                        break;
                    }
                    $errors['global'][] = ['global', $globalError['code'], $globalError['message']];
                    $limit--;
                }
            }
        }

        return $errors;
    }
}
