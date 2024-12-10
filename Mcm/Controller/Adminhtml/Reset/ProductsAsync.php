<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Controller\Adminhtml\Reset;

use Mirakl\Mcm\Controller\Adminhtml\AbstractController;

class ProductsAsync extends AbstractController
{
    /**
     * Resets last synchronization date of MCM products async import
     */
    public function execute()
    {
        $this->connectorConfig->resetSyncDate('mcm_products_import_async');

        $this->messageManager->addSuccessMessage(
            __('Last MCM products synchronization date has been reset successfully.')
        );

        return $this->redirectReferer();
    }
}
