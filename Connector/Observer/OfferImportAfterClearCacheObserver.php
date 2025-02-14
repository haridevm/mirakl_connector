<?php

declare(strict_types=1);

namespace Mirakl\Connector\Observer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Mirakl\Connector\Helper\Config;
use Mirakl\Process\Model\Process;

class OfferImportAfterClearCacheObserver implements ObserverInterface
{
    /**
     * @var int
     */
    public const CLEAN_CACHE_CHUNK_SIZE = 200;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var int
     */
    private int $chunkSize;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param TypeListInterface        $typeList
     * @param Config                   $config
     * @param ManagerInterface         $eventManager
     * @param int                      $chunkSize
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        TypeListInterface $typeList,
        Config $config,
        ManagerInterface $eventManager,
        int $chunkSize = self::CLEAN_CACHE_CHUNK_SIZE
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->typeList = $typeList;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $skus = $observer->getEvent()->getSkus();
        if (empty($skus)) {
            return;
        }

        if (!$this->config->isOffersImportClearCache()) {
            $this->typeList->invalidate([
                Collection::TYPE_IDENTIFIER,
                Block::TYPE_IDENTIFIER,
                PageCache::TYPE_IDENTIFIER,
            ]);

            return;
        }

        /** @var Process $process */
        $process = $observer->getEvent()->getProcess();

        $process->output(__('Updating cache for products...'), true);

        foreach (array_chunk($skus, $this->chunkSize) as $chunkSkus) {
            $products = $this->productCollectionFactory->create()
                ->addAttributeToFilter('sku', ['in' => $chunkSkus]);

            foreach ($products as $product) {
                /** @see \Magento\Framework\Model\AbstractModel::afterSave() */
                /** @var \Magento\Catalog\Model\Product $product */
                $product->cleanModelCache();
                $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
            }
        }

        $process->output(__('Done!'));
    }
}
