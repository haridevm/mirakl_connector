<?php
namespace Mirakl\Mci\Observer\Process;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Mci\Console\Command\Product\Import\ImagesCommand;
use Mirakl\Mci\Helper\Product\Image as ImageHelper;
use Mirakl\Process\Model\Process;

class TimeoutObserver implements ObserverInterface
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(ImageHelper $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Process $process */
        $process = $observer->getProcess();

        $this->handleImagesImportTimeout($process);
    }

    /**
     * If an images import has failed for some reason, all the products images have not been imported in Magento.
     * The goal of this method is to retrieve the list of unprocessed products and to update their status to "pending".
     * Then, they will be retrieved in the next images import command and will be processed normally.
     *
     * @param Process $process
     */
    protected function handleImagesImportTimeout(Process $process)
    {
        if ($process->getName() !== ImagesCommand::PROCESS_NAME) {
            return;
        }

        $params = $process->getParams();

        if (empty($params[0]) || !is_array($params[0])) {
            return; // If first parameter is not a list of product ids, abort.
        }

        // Retrieve product ids from process params
        $productIds = $params[0];

        // Retrieve products that have not been processed in images import
        $collection = $this->imageHelper->getProductsByImagesStatus(ImageHelper::IMAGES_IMPORT_STATUS_PROCESSING);
        $collection->addIdFilter($productIds);

        // Switch processing products status to "pending" in order to retrieve them in the next images import command
        $this->imageHelper->markProductsImagesAsPending($collection);
    }
}
