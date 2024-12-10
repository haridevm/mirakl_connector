<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\OfferIndexer\Model\Indexer\Offer\IndexProcessor;

class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var IndexProcessor
     */
    private $indexProcessor;

    /**
     * @param IndexProcessor $indexProcessor
     */
    public function __construct(IndexProcessor $indexProcessor)
    {
        $this->indexProcessor = $indexProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if (!$product->getId()) {
            return;
        }

        if ($product->getTypeId() === Product\Type::TYPE_SIMPLE) {
            $this->indexProcessor->reindexRow($product->getId());
        } elseif ($product->getTypeId() === ConfigurableType::TYPE_CODE) {
            /** @var ConfigurableType $productType */
            $productType = $product->getTypeInstance();
            $childrenIds = $productType->getUsedProductCollection($product)->getColumnValues('entity_id');
            if (!empty($childrenIds)) {
                $this->indexProcessor->reindexList($childrenIds);
            }
        }
    }
}
