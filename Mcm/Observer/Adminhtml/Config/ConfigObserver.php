<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Adminhtml\Config;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriterInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mirakl\Catalog\Helper\Config as CatalogConfigHelper;
use Mirakl\Mci\Helper\Data as MciHelper;
use Mirakl\Mcm\Helper\Config as McmConfigHelper;
use Mirakl\Mcm\Helper\Data as McmHelper;

class ConfigObserver implements ObserverInterface
{
    /**
     * @var CatalogConfigHelper
     */
    protected $catalogConfigHelper;

    /**
     * @var McmConfigHelper
     */
    protected $mcmConfigHelper;

    /**
     * @var ConfigWriterInterface
     */
    protected $configWriter;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var EavSetup
     */
    protected $eavSetup;

    /**
     * @param CatalogConfigHelper   $catalogConfigHelper
     * @param McmConfigHelper       $mcmConfigHelper
     * @param ConfigWriterInterface $configWriter
     * @param ManagerInterface      $messageManager
     * @param TypeListInterface     $cacheTypeList
     * @param EavSetup              $eavSetup
     */
    public function __construct(
        CatalogConfigHelper $catalogConfigHelper,
        McmConfigHelper $mcmConfigHelper,
        ConfigWriterInterface $configWriter,
        ManagerInterface $messageManager,
        TypeListInterface $cacheTypeList,
        EavSetup $eavSetup
    ) {
        $this->catalogConfigHelper = $catalogConfigHelper;
        $this->mcmConfigHelper = $mcmConfigHelper;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->eavSetup = $eavSetup;
    }

    /**
     * @param string $message
     * @return ManagerInterface
     */
    private function addWarningMessage($message)
    {
        return $this->messageManager->addWarningMessage(__($message));
    }

    /**
     * @return void
     */
    private function cleanConfigCache()
    {
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @inheritdoc
     */
    public function execute(EventObserver $observer)
    {
        if ($this->mcmConfigHelper->isMcmEnabled() || $this->mcmConfigHelper->isAsyncMcmEnabled()) {
            if ($this->catalogConfigHelper->isSyncCategories()) {
                $this->saveConfig(CatalogConfigHelper::XML_PATH_ENABLE_SYNC_CATEGORIES, '0');
                // phpcs:ignore
                $this->addWarningMessage('MCM configuration is enabled: Mirakl automatically disabled Marketplace Categories Synchronization (CA01)');
                $this->cleanConfigCache();
            }
            if ($this->catalogConfigHelper->isSyncProducts()) {
                $this->saveConfig(CatalogConfigHelper::XML_PATH_ENABLE_SYNC_PRODUCTS, '0');
                // phpcs:ignore
                $this->addWarningMessage('MCM configuration is enabled: Mirakl automatically disabled Products Synchronization (P21)');
                $this->cleanConfigCache();
            }
            if (!$this->mcmConfigHelper->isSyncMcmProducts()) {
                $this->saveConfig(McmConfigHelper::XML_PATH_ENABLE_SYNC_MCM_PRODUCTS, '1');
                // phpcs:ignore
                $this->addWarningMessage('MCM configuration is enabled: Mirakl automatically enabled MCM Products Export (CM21)');
                $this->cleanConfigCache();
            }
        } elseif ($this->mcmConfigHelper->isSyncMcmProducts()) {
            $this->saveConfig(McmConfigHelper::XML_PATH_ENABLE_SYNC_MCM_PRODUCTS, '0');
            // phpcs:ignore
            $this->addWarningMessage('MCM configuration is disabled: Mirakl automatically disabled MCM Products Export (CM21)');
            $this->cleanConfigCache();
        }

        $this->updateMiraklAttributes();
    }

    /**
     * @param string $path
     * @param mixed  $value
     * @return void
     */
    private function saveConfig($path, $value)
    {
        $this->configWriter->save($path, $value);
    }

    /**
     * @return void
     */
    private function updateMiraklAttributes()
    {
        $isMcmEnabled = $this->mcmConfigHelper->isMcmEnabled() || $this->mcmConfigHelper->isAsyncMcmEnabled();

        $update = [
            'is_visible' => [
                'mirakl_authorized_shop_ids'                   => (int) !$isMcmEnabled,
                MciHelper::ATTRIBUTE_SHOPS_SKUS                => (int) !$isMcmEnabled,
                MciHelper::ATTRIBUTE_VARIANT_GROUP_CODES       => (int) !$isMcmEnabled,
                McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER => (int) $isMcmEnabled,
                McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID         => (int) $isMcmEnabled,
                McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE => (int) $isMcmEnabled,
            ],
        ];

        foreach ($update as $field => $values) {
            foreach ($values as $code => $value) {
                $this->eavSetup->updateAttribute(Product::ENTITY, $code, $field, $value);
            }
        }
    }
}
