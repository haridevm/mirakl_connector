<?php

declare(strict_types=1);

namespace Mirakl\Mci\Controller\Adminhtml\Sync;

use Mirakl\Connector\Controller\Adminhtml\AbstractSync;

class Images extends AbstractSync
{
    /**
     * Download and import pending products images into Magento
     */
    public function execute()
    {
        try {
            /** @var \Mirakl\Mci\Helper\Config $mciConfig */
            $mciConfig = $this->_objectManager->get(\Mirakl\Mci\Helper\Config::class);
            $limit = $mciConfig->getImagesImportLimit();

            /** @var \Mirakl\Mci\Helper\Product\Image\Process $imageProcessHelper */
            $imageProcessHelper = $this->_objectManager->get(\Mirakl\Mci\Helper\Product\Image\Process::class);
            $process = $imageProcessHelper->createImportProcess($limit);

            $this->messageManager->addSuccessMessage(
                __('Images will be downloaded and imported in background.')
            );
            $this->addRawSuccessMessage(
                __('Click <a href="%1">here</a> to view process output.', $process->getUrl())
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred while importing images (%1).', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }
}
