<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class DownloadOutput extends AbstractProcessAction implements HttpGetActionInterface
{
    /**
     * @inheritdoc
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
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $process->getOutputSize())
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $this->_session->writeClose();
        echo $process->getOutput(); // phpcs:ignore

        // Nothing more to do
        exit;  // phpcs:ignore
    }
}
