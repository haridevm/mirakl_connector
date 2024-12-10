<?php

declare(strict_types=1);

namespace Mirakl\Event\Controller\Adminhtml\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Core\Controller\Adminhtml\RawMessagesTrait;
use Mirakl\Process\Model\Process;

class Run extends AbstractEventAction implements HttpGetActionInterface
{
    use RawMessagesTrait;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $process = $this->getEventHelper()->getOrCreateEventProcess(Process::TYPE_ADMIN);
            $process->execute();
            $this->messageManager->addSuccessMessage(__('Events workflow has been run successfully.'));
            $this->addRawSuccessMessage(
                __('Click <a href="%1">here</a> to view process output.', $process->getUrl())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while running events workflow: %1.', $e->getMessage())
            );
        }

        return $this->_redirect('*/*/');
    }
}
