<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Delete;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Magento\Framework\Event\ManagerInterface;
use Mirakl\Mcm\Helper\Config as McmConfig;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Process\Model\Process as ProcessModel;
use Mirakl\Api\Helper\Mcm\Product as Api;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Handler
{
    public const CODE = 'CM61';

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var McmConfig
     */
    private $mcmConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Api                      $api
     * @param ManagerInterface         $eventManager
     * @param McmConfig                $mcmConfig
     * @param State                    $appState
     * @param ProductResource          $productResource
     * @param Registry                 $registry
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        Api $api,
        ManagerInterface $eventManager,
        McmConfig $mcmConfig,
        State $appState,
        ProductResource $productResource,
        Registry $registry
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->api                      = $api;
        $this->eventManager             = $eventManager;
        $this->mcmConfig                = $mcmConfig;
        $this->appState                 = $appState;
        $this->productResource          = $productResource;
        $this->registry                 = $registry;
    }

    /**
     * @param ProcessModel   $process
     * @param \DateTime|null $deletedFrom
     * @return int|false
     */
    public function run(ProcessModel $process, \DateTime $deletedFrom = null)
    {
        if (!$this->mcmConfig->isMcmEnabled() && !$this->mcmConfig->isAsyncMcmEnabled()) {
            $process->output(__('Module MCM is disabled. See your Mirakl MCM configuration'));

            return false;
        }

        if (!$this->mcmConfig->isMcmProductsDeleteEnabled()) {
            $process->output(__('MCM products deletion is disabled. See your Mirakl synchronization configuration'));

            return false;
        }

        // Set area code if not already set to allow products delete
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Ignore error
        }

        // register area as secure to allow remove action
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        if ($deletedFrom) {
            $process->output(__('Removing deleted products in Mirakl since %1', $deletedFrom->format('Y-m-d H:i:s')));
        } else {
            $process->output(__('Removing all products deleted in Mirakl'));
        }

        $process->output(__('Preparing products to delete...'));

        $deleteProducts = $this->api->getDeleteProducts($deletedFrom);

        $mcmProductIds = [];
        foreach ($deleteProducts as $deleteProduct) {
            $mcmProductIds[] = $deleteProduct['mirakl_product_id'];
        }

        $deleteCollection = $this->getCollection($mcmProductIds);
        if (!$deleteCollection->count()) {
            $process->output(__('Nothing to delete'));

            return false;
        }

        $process->output(__('Found %1 product(s) to delete', $deleteCollection->count()));

        $deletedCount = $this->delete($deleteCollection, $process);

        $process->output(__('Done! (%1)', $deletedCount));

        return $deletedCount;
    }

    /**
     * Retrieves Magento MCM products collection to delete
     *
     * @param string[] $mcmProductIds
     * @return ProductCollection
     */
    private function getCollection(array $mcmProductIds)
    {
        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();

        if (empty($mcmProductIds)) {
            $collection->addIdFilter(0); // Workaround for empty collection
        }

        $collection->addAttributeToFilter(McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID, ['in' => $mcmProductIds]);

        $this->eventManager->dispatch('mirakl_get_mcm_products_to_delete_after', [
            'collection'      => $collection,
            'mcm_product_ids' => $mcmProductIds,
        ]);

        return $collection;
    }

    /**
     * Delete products collection
     *
     * @param ProductCollection $collection
     * @param ProcessModel      $process
     * @return int
     */
    private function delete(ProductCollection $collection, ProcessModel $process)
    {
        $this->eventManager->dispatch('mirakl_delete_mcm_products_before', ['collection' => $collection]);

        $count = 0;
        /** @var Product $product */
        foreach ($collection as $product) {
            try {
                $this->productResource->delete($product);
                $process->output(
                    __(
                        'Product %1 deleted (id: %2, SKU: %3)',
                        $product->getData(McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID),
                        $product->getId(),
                        $product->getSku()
                    )
                );
                $count++;
            } catch (\Exception $e) {
                $process->output(
                    __(
                        'An error occurred while deleting product %1 (id: %2, SKU: %3): ' . $e->getMessage(),
                        $product->getData(McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID),
                        $product->getId(),
                        $product->getSku()
                    )
                );
            }
        }

        return $count;
    }
}
