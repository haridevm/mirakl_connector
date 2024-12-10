<?php
namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirakl\Core\Model\File\CsvFileTrait;

/**
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class ShowFile extends AbstractProcessAction
{
    use CsvFileTrait;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param   Context     $context
     * @param   Filesystem  $filesystem
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
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

        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $this->getRequest()->getParam('mirakl', false) ? $process->getMiraklFile() : $process->getFile();
        $file = $directory->openFile($path);

        if (pathinfo($path, PATHINFO_EXTENSION) === 'json') {
            // Show a JSON file
            $contents = json_decode($file->readAll(), true);
            $body = '<pre>' . htmlentities(json_encode($contents, JSON_PRETTY_PRINT)) . '</pre>';
        } else {
            if (count($this->readCsv($file)) > 1) {
                // Parse CSV and show as HTML table
                $file->seek(0);
                $body = '<table border="1" cellpadding="2" style="border-collapse: collapse; border: 1px solid #aaa;">';
                while ($data = $this->readCsv($file)) {
                    $body .= sprintf('<tr>%s</tr>', implode('', array_map(function ($value) {
                        if (preg_match('#^(https?:\/\/.+)$#', $value)) {
                            $value = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $value);
                        } else {
                            $value = htmlspecialchars($value);
                        }

                        return '<td>' . $value . '</td>';
                    }, $data)));
                }
                $body .= '</table>';
            } else {
                // Show raw contents
                $body = '<pre>' . htmlentities($file->readAll()) . '</pre>';
            }
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($body)
            ->sendResponse();
    }
}
