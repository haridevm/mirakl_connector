<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Framework\App\Action\HttpGetActionInterface;

class CheckMiraklStatus extends AbstractProcessAction implements HttpGetActionInterface
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

        if (!$process->canCheckMiraklStatus()) {
            return $this->redirectError(__('Mirakl status cannot be checked on this process.'));
        }

        try {
            $process->checkMiraklStatus();
            $this->messageManager->addSuccessMessage(__('Mirakl status has been updated successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while checking Mirakl status of the process: %1.', $e->getMessage())
            );
        }

        return $this->_redirect('*/*/view', ['id' => $process->getId()]);
    }
}
