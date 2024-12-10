<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends AbstractProcessAction implements HttpPostActionInterface
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

        try {
            $this->getProcessResource()->delete($process);
            $this->messageManager->addSuccessMessage(__('Process has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting the process: %1.', $e->getMessage())
            );
        }

        return $this->_redirect('*/*/');
    }
}
