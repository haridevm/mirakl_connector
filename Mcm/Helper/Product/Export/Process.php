<?php
namespace Mirakl\Mcm\Helper\Product\Export;

use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Api\Helper\Mcm\Product as Api;
use Mirakl\Core\Model\ResourceModel\Product\Collection as ProductCollection;
use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirakl\MCM\Front\Domain\Product\Export\ProductAcceptanceStatus as ProductAcceptance;
use Mirakl\Mcm\Helper\Config as McmConfig;
use Mirakl\Mci\Helper\Hierarchy as HierarchyHelper;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Helper\Product\Export\Product as ProductHelper;
use Mirakl\Mcm\Model\Product\Export\Formatter;
use Mirakl\Process\Model\Process as ProcessModel;

class Process extends AbstractHelper
{
    const CODE = 'CM21';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var CategoryResourceFactory
     */
    protected $categoryResourceFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var HierarchyHelper
     */
    protected $hierarchyHelper;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var McmConfig
     */
    protected $mcmConfig;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @param Context                  $context
     * @param StoreManagerInterface    $storeManager
     * @param CategoryResourceFactory  $categoryResourceFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductHelper            $productHelper
     * @param HierarchyHelper          $hierarchyHelper
     * @param Api                      $api
     * @param McmConfig                $mcmConfig
     * @param Formatter                $formatter
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CategoryResourceFactory $categoryResourceFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductHelper $productHelper,
        HierarchyHelper $hierarchyHelper,
        Api $api,
        McmConfig $mcmConfig,
        Formatter $formatter
    ) {
        parent::__construct($context);
        $this->storeManager             = $storeManager;
        $this->categoryResourceFactory  = $categoryResourceFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productHelper            = $productHelper;
        $this->hierarchyHelper          = $hierarchyHelper;
        $this->api                      = $api;
        $this->mcmConfig                = $mcmConfig;
        $this->formatter                = $formatter;
    }

    /**
     * @param   ProcessModel    $process
     * @return  bool
     */
    public function exportAll(ProcessModel $process)
    {
        $products = $this->getProductsToExport();
        if (!$size = $products->getSize()) {
            $process->output(__('Nothing to export'));

            return false;
        }
        $process->output(__('Products to export: %1', $size));

        $chunkSize = $this->mcmConfig->getMcmProductExportChunkSize();
        $process->output(__('Max products per chunk : %1', $chunkSize));

        $this->_eventManager->dispatch('mirakl_export_mcm_products_before', ['collection' => $products]);

        $nbProductExported = 0;
        for ($chunk = 0; ($offset = $chunk * $chunkSize) < $size; $chunk++) {
            $nbProductExported += $this->exportChunk($process, $products, $chunkSize, $offset);
        }

        $process->output('-----');
        $process->output(__('Export completed'));
        $process->output(__('%1 product(s) exported to Mirakl', $nbProductExported));

        return true;
    }

    /**
     * @param   ProcessModel      $process
     * @param   ProductCollection $collection
     * @param   int               $limit
     * @param   int               $offset
     * @return  int
     */
    public function exportChunk(ProcessModel $process, ProductCollection $collection, $limit, $offset)
    {
        $process->output('-----');
        $process->output(__('Chunk %1', $offset / $limit + 1));
        $process->output(__('Loading products %1 to %2...', $offset + 1, $offset + $limit));


        $productIds = $collection->getAllIds($limit, $offset);
        $synchroId = $this->exportProducts($productIds, ProductAcceptance::STATUS_ACCEPTED, false, $process);

        $process->output(__('Done! (tracking id: %1)', $synchroId), true);

        return count($productIds);
    }

    /**
     * Exports an unique product to Mirakl platform
     *
     * @param   int     $productId
     * @param   string  $acceptance
     * @return  int|false
     */
    public function exportProduct($productId, $acceptance = ProductAcceptance::STATUS_ACCEPTED)
    {
        return $this->exportProducts([$productId], $acceptance);
    }

    /**
     * Exports specified product ids to Mirakl platform
     *
     * @param   array               $productIds
     * @param   string              $acceptance
     * @param   bool                $forceOperatorMaster
     * @param   ProcessModel|null   $process
     * @param   array               $overrideData
     * @return  int|false
     */
    public function exportProducts(
        array $productIds,
        $acceptance = ProductAcceptance::STATUS_ACCEPTED,
        $forceOperatorMaster = false,
        $process = null, // Attribute placed at end with null default value for retro-compatibility
        array $overrideData = []
    ) {
        if (empty($productIds)) {
            if ($process) {
                $process->output(__('Nothing to export'));
            }
            return false;
        }

        // Retrieve products data as array
        $products = $this->productHelper->getProductsData($productIds);

        if ($process) {
            $process->output(__('%1 product(s) loaded', count($products)));
            $process->output(__('Transforming products to CM21 json format...'));
        }
        $data = [];
        foreach ($products as $product) {
            foreach ($overrideData as $attributeCode => $value) {
                if (isset($product[$attributeCode])) {
                    $product[$attributeCode] = $value;
                }
            }
            $data[] = $this->prepare($product, $acceptance, $forceOperatorMaster);
        }

        if ($process) {
            $process->output(__('Sending products to Mirakl...'));
        }

        return $this->api->export($data);
    }

    /**
     * Exports custom product collection to Mirakl platform
     *
     * @param   ProductCollection   $collection
     * @param   string              $acceptance
     * @param   bool                $forceOperatorMaster
     * @return  int|false
     */
    public function exportCollection(
        ProductCollection $collection,
        $acceptance = ProductAcceptance::STATUS_ACCEPTED,
        $forceOperatorMaster = false
    ) {
        $this->_eventManager->dispatch('mirakl_export_mcm_products_before', ['collection' => $collection]);

        return $this->exportProducts($collection->getAllIds(), $acceptance, $forceOperatorMaster);
    }

    /**
     * Retrieves Magento MCM products available for being exported to Mirakl platform
     *
     * @return  ProductCollection
     */
    public function getProductsToExport()
    {
        // 1. Inititalize product collection
        $collection = $this->productCollectionFactory->create();

        // 2. Retrieve category ids from MCI catalog categories
        $categoryIds = $this->hierarchyHelper->getTree()->getCollection()->getAllIds();

        // 3. Get product ids of categories retrieved above (use index table to handle anchor categories)
        $productIds = [];
        if (!empty($categoryIds)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Category $resource */
            $resource   = $this->categoryResourceFactory->create();
            $connection = $resource->getConnection();
            $select     = $connection->select()
                ->from($resource->getTable('catalog_category_product'), 'product_id')
                ->where('category_id IN (?)', $categoryIds);
            $productIds = array_unique($connection->fetchCol($select));
        }

        if (empty($productIds)) {
            $productIds = [0]; // Workaround for empty collection
        }

        // 4. Add some conditions to the product collection
        // Filtering by mirakl_mcm_is_operator_master = true but also need to filter
        // by 'IS NULL' because it is the default value defined when the attribute is created
        $collection->addIdFilter($productIds)
            ->addFieldToFilter('type_id', 'simple')
            ->addFieldToFilter('status', 1)
            ->addAttributeToFilter('mirakl_sync', 1)
            ->addAttributeToFilter([
                ['attribute' => McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER, 'eq' => 1],
                ['attribute' => McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER, 'null' => true],
            ], null, 'left');

        $this->_eventManager->dispatch('mirakl_get_mcm_products_to_export_after', [
            'collection'   => $collection,
            'category_ids' => $categoryIds,
        ]);

        return $collection;
    }

    /**
     * @param   array   $product
     * @return  bool
     */
    protected function isSyncProduct(array $product)
    {
        // Consider that mirakl_sync is enabled if provided data do not contain the mirakl_sync key
        return !isset($product['mirakl_sync']) || $product['mirakl_sync'];
    }

    /**
     * @param   array   $product
     * @return  bool
     */
    protected function isOperatorMasterProduct(array $product)
    {
        // Consider that mirakl_mcm_is_operator_master is enabled if provided data do not contain the mirakl_mcm_is_operator_master key
        return !isset($product[McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER])
            || $product[McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER];
    }

    /**
     * Prepares product data for export
     *
     * @param   array   $product
     * @param   string  $acceptance
     * @param   bool    $forceOperatorMaster
     * @return  array
     */
    public function prepare(
        array $product,
        $acceptance = ProductAcceptance::STATUS_ACCEPTED,
        $forceOperatorMaster = false
    ) {
        $result = [
            'mirakl_product_id' => $product[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID], // if null = creation else update
            'product_sku'       => $product[McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_SKU],
        ];

        $isSyncProduct = $this->isSyncProduct($product);
        $isOperatorMaster = $this->isOperatorMasterProduct($product);

        // Do not send internal Magento data to Mirakl
        unset($product[McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER], $product['mirakl_sync']);

        if ($forceOperatorMaster ||
            ($isSyncProduct && $isOperatorMaster && $acceptance == ProductAcceptance::STATUS_ACCEPTED)
        ) {
            // Add product's data
            $result['data'] = $this->prepareProductData($product);
        }

        if (!$isSyncProduct) {
            // Flag product as rejected in MCM if product is not flagged for sync
            $acceptance = ProductAcceptance::STATUS_REJECTED;
        }

        if ($result['mirakl_product_id'] || $acceptance == ProductAcceptance::STATUS_REJECTED) {
            // Send acceptance value only if a mirakl_product_id is defined on product
            $result['acceptance']['status'] = $acceptance;
        }

        return $result;
    }

    /**
     * Returns formatted product's data
     *
     * @param   array   $data
     * @return  array
     */
    protected function prepareProductData(array $data)
    {
        $this->formatter->format($data);

        return $data;
    }

    /**
     * Prepares product data for export
     *
     * @param   int     $productId
     * @param   string  $acceptance
     * @return  array
     */
    public function prepareProductFromId($productId, $acceptance = ProductAcceptance::STATUS_ACCEPTED)
    {
        $product = $this->productHelper->getSingleProductData($productId);

        return $this->prepare($product, $acceptance);
    }

    /**
     * @param   int $productId
     * @return  int|false
     */
    public function rejectProduct($productId)
    {
        return $this->exportProduct($productId, ProductAcceptance::STATUS_REJECTED);
    }

    /**
     * @param   array   $productIds
     * @return  int|false
     */
    public function rejectProducts(array $productIds)
    {
        return $this->exportProducts($productIds, ProductAcceptance::STATUS_REJECTED);
    }
}
