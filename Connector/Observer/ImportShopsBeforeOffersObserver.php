<?php
declare(strict_types=1);

namespace Mirakl\Connector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Connector\Model\Shop\Import\Handler as ShopImportHandler;

class ImportShopsBeforeOffersObserver implements ObserverInterface
{
    /**
     * @var ShopImportHandler
     */
    private $shopImportHandler;

    /**
     * @param ShopImportHandler $shopImportHandler
     */
    public function __construct(ShopImportHandler $shopImportHandler)
    {
        $this->shopImportHandler = $shopImportHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Mirakl\Process\Model\Process $process */
        $process = $observer->getEvent()->getProcess();
        $process->output(__('Synchronizing shops automatically before offers import...'));
        $this->shopImportHandler->execute($process);
    }
}
