<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Product\Import\Bulk;

use Magento\Framework\Event\Observer;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter\EntityAdapterInterface;

class AfterImportDataObserver extends \Magento\CatalogUrlRewrite\Observer\AfterImportDataObserver
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $adapter = $observer->getEvent()->getAdapter();

        if (!$adapter instanceof EntityAdapterInterface) {
            // Execute the default observer only if the adapter is NOT the one used for MCM products import
            parent::execute($observer);
        }
    }
}
