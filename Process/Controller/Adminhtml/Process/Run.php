<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Run extends AbstractProcessAction implements HttpGetActionInterface
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

        if (!$process->canRun()) {
            return $this->redirectError(__('This process cannot be executed.'));
        }

        try {
            $process->run(true);
            $this->messageManager->addSuccessMessage(__('Process has been executed successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while executing the process: %1.', $e->getMessage())
            );
        }

        return $this->_redirect('*/*/view', ['id' => $process->getId()]);
    }
}
