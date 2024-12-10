<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\Indexer\Offer;

use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory as OfferCollectionFactory;
use Mirakl\OfferIndexer\Model\Indexer\Offer\Collection\Customizer\CustomizerInterface;
use Mirakl\OfferIndexer\Model\Indexer\Offer\Handler\IndexHandlerInterface;

class Indexer implements IndexerInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OfferCollectionFactory
     */
    private $offerCollectionFactory;

    /**
     * @var CustomizerInterface[]
     */
    private $collectionCustomizers;

    /**
     * @var IndexHandlerInterface[]
     */
    private $indexHandlers;

    /**
     * @param StoreManagerInterface  $storeManager
     * @param OfferCollectionFactory $offerCollectionFactory
     * @param array                  $collectionCustomizers
     * @param array                  $indexHandlers
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        OfferCollectionFactory $offerCollectionFactory,
        array $collectionCustomizers = [],
        array $indexHandlers = []
    ) {
        $this->storeManager = $storeManager;
        $this->offerCollectionFactory = $offerCollectionFactory;
        $this->collectionCustomizers = $collectionCustomizers;
        $this->indexHandlers = $indexHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(array $skus = []): void
    {
        foreach ($this->indexHandlers as $indexHandler) {
            $indexHandler->clear($skus);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $skus = []): void
    {
        $this->clear($skus);

        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->getIsActive()) {
                continue;
            }

            $collection = $this->offerCollectionFactory->create();
            $collection->setStoreId($store->getId());

            if (!empty($skus)) {
                $collection->addProductSkuFilter($skus);
            }

            foreach ($this->collectionCustomizers as $customizer) {
                $customizer->customize($collection, $store);
            }

            foreach ($this->indexHandlers as $indexHandler) {
                $data = $indexHandler->build($collection, $store);
                $indexHandler->update($data);
            }
        }
    }
}
