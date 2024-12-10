<?php
namespace Mirakl\Mcm\Controller\Adminhtml\Sync\Mcm;

use Mirakl\Connector\Controller\Adminhtml\AbstractSync;
use Mirakl\Mcm\Model\Product\Delete\Handler;
use Mirakl\Process\Model\Process;

class ProductsDelete extends AbstractSync
{
    /**
     * Remove deleted MCM products
     */
    public function execute()
    {
        try {
            if (!$this->checkConnectorEnabled()) {
                return $this->redirectReferer();
            }

            $deleteFrom = $this->connectorConfig->getSyncDate('mcm_products_delete');

            /** @var Process $process */
            $process = $this->processFactory->create()
                ->setType(Process::TYPE_ADMIN)
                ->setCode(Handler::CODE)
                ->setName('CM61 synchronization')
                ->setHelper(Handler::class)
                ->setMethod('run')
                ->setParams([$deleteFrom]);

            $this->processResourceFactory->create()->save($process);
            $this->connectorConfig->setSyncDate('mcm_products_delete');
            $this->messageManager->addSuccessMessage(__('Products will be deleted in background.'));
            $this->addRawSuccessMessage(__('Click <a href="%1">here</a> to view process output.', $process->getUrl()));

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting products (%1).', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }
}