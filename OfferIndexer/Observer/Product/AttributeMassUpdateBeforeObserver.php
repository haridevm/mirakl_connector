<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Observer\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexProcessor;

class AttributeMassUpdateBeforeObserver implements ObserverInterface
{
    /**
     * @var IndexProcessor
     */
    private $indexProcessor;

    /**
     * @var array
     */
    private $attributesFilter;

    /**
     * @param IndexProcessor $indexProcessor
     * @param array $attributesFilter
     */
    public function __construct(
        IndexProcessor $indexProcessor,
        array $attributesFilter = []
    ) {
        $this->indexProcessor = $indexProcessor;
        $this->attributesFilter = $attributesFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();
        $attributesData = $observer->getEvent()->getAttributesData();

        if (!empty($productIds) && !empty(array_intersect_key($attributesData, $this->attributesFilter))) {
            $this->indexProcessor->reindexList($productIds);
        }
    }
}
