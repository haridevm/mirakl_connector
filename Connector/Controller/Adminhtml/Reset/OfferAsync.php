<?php
namespace Mirakl\Connector\Controller\Adminhtml\Reset;

use Mirakl\Connector\Controller\Adminhtml\AbstractReset;

class OfferAsync extends AbstractReset
{
    /**
     * Resets last synchronization date of offers
     */
    public function execute()
    {
        $this->connectorConfig->resetSyncDate('offers_async');

        $this->messageManager->addSuccessMessage(__('Last offers synchronization date has been reset successfully.'));

        return $this->redirectReferer();
    }
}
