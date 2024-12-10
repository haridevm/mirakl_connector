<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\MCM\Front\Domain\Product\Export\ProductAcceptanceStatus as ProductAcceptance;

class VariantGroupCodeCleanAfterObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $process = $observer->getEvent()->getData('process');
        $productIds = $observer->getEvent()->getData('product_ids');

        if (!$this->isEnabled() || empty($productIds)) {
            return;
        }

        $synchroId = $this->processHelper->exportProducts(
            $productIds,
            ProductAcceptance::STATUS_ACCEPTED,
            false,
            $process
        );

        $process->output(__('Done! (tracking id: %1)', $synchroId), true);
    }
}
