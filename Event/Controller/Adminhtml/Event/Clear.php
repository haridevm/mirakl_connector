<?php

declare(strict_types=1);

namespace Mirakl\Event\Controller\Adminhtml\Event;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Clear extends AbstractEventAction implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $this->getEventResource()->truncate();
            $this->messageManager->addSuccessMessage(__('Events have been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting all events: %1.', $e->getMessage())
            );
        }

        return $this->_redirect('*/*/');
    }
}
