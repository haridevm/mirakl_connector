<?php

declare(strict_types=1);

namespace Mirakl\Connector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Connector\Helper\Config;

class LockOrderItemObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();

        if (!$item || !$item->getMiraklOfferId()) {
            return;
        }

        if ($this->config->isLockMiraklItemsInvoicing($item->getStoreId())) {
            $item->setLockedDoInvoice(true);
        }

        if ($this->config->isLockMiraklItemsShipping($item->getStoreId())) {
            $item->setLockedDoShip(true);
        }
    }
}
