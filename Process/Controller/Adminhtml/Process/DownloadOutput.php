<?php
namespace Mirakl\Process\Controller\Adminhtml\Process;

/**
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class DownloadOutput extends AbstractProcessAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $process = $this->getProcess();

        if (!$process->getId()) {
            return $this->redirectError(__('This process no longer exists.'));
        }

        $fileName = sprintf('process_output_%d.log', $process->getId());

        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0',true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $process->getOutputSize())
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $this->_session->writeClose();
        echo $process->getOutput();
    }
}
