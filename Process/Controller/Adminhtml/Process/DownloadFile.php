<?php
namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Mirakl\Process\Helper\Data;

/**
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class DownloadFile extends AbstractProcessAction
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context    $context
     * @param Filesystem $filesystem
     * @param Data       $helper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Data $helper
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $process = $this->getProcess();

        if (!$process->getId()) {
            return $this->redirectError(__('This process no longer exists.'));
        }

        $path = $this->getRequest()->getParam('mirakl', false) ? $process->getMiraklFile() : $process->getFile();
        if (!$path) {
            return $this->redirectError(__('File does not exist.'), true);
        }

        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $file = $directory->openFile($path);

        /** @var Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $fileName = pathinfo($path, PATHINFO_BASENAME);

        $result->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $this->helper->getFileSize($path))
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $result->setContents($file->readAll());

        return $result;
    }
}
