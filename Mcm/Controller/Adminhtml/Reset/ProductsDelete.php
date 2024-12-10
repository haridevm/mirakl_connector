<?php
namespace Mirakl\Mcm\Controller\Adminhtml\Reset;

use Mirakl\Mcm\Controller\Adminhtml\AbstractController;

class ProductsDelete extends AbstractController
{
    /**
     * Resets last synchronization date of MCM products deletion
     */
    public function execute()
    {
        $this->connectorConfig->resetSyncDate('mcm_products_delete');

        $this->messageManager->addSuccessMessage(__('Last MCM products delete synchronization date has been reset successfully.'));

        return $this->redirectReferer();
    }
}
