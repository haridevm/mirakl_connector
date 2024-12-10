<?php

declare(strict_types=1);

namespace Mirakl\Connector\Controller\Adminhtml\Sync;

use Mirakl\Connector\Controller\Adminhtml\AbstractSync;
use Mirakl\Process\Model\Process;

class OfferAsync extends AbstractSync
{
    /**
     * Synchronize Mirakl offers asynchronously into Magento
     */
    public function execute()
    {
        try {
            if (!$this->checkConnectorEnabled()) {
                return $this->redirectReferer();
            }

            /** @var Process $process */
            $process = $this->processFactory->create()
                ->setType(Process::TYPE_ADMIN)
                ->setName('OF52-OF53-OF54 synchronization')
                ->setHelper('Mirakl\Connector\Model\Offer\AsyncImport\Import')
                ->setMethod('execute');

            $this->processResourceFactory->create()->save($process);
            $this->messageManager->addSuccessMessage(__('Offers will be imported in background.'));
            $this->addRawSuccessMessage(__('Click <a href="%1">here</a> to view process output.', $process->getUrl()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while synchronizing offers (%1).', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }
}
