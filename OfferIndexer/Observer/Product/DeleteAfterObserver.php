<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexerInterface;

class DeleteAfterObserver implements ObserverInterface
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @param IndexerInterface $indexer
     */
    public function __construct(IndexerInterface $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($sku = $product->getSku()) {
            $this->indexer->clear([$sku]);
        }
    }
}
