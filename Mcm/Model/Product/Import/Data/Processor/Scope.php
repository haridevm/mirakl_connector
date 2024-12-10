<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Mcm\Helper\Config;

class Scope implements ProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreInterface[]
     */
    private $stores;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config                $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        $data['stores'] = $this->getStoreCodes();
        $data['websites'] = $this->getWebsiteCodes();
    }

    /**
     * Returns stores used for product import
     *
     * @return StoreInterface[]
     */
    private function getStores(): array
    {
        if (null === $this->stores) {
            $this->stores = $this->config->getStoresUsedForProductImport(false);
        }

        return $this->stores;
    }

    /**
     * @return array
     */
    private function getStoreCodes(): array
    {
        $storeCodes = [];
        if ($this->storeManager->isSingleStoreMode()) {
            $storeCodes[] = $this->storeManager->getDefaultStoreView()->getCode();
        } else {
            foreach ($this->getStores() as $store) {
                if ($storeCode = $store->getCode()) {
                    $storeCodes[] = $storeCode;
                }
            }
        }

        return $storeCodes;
    }

    /**
     * Returns website codes to enable for product import
     *
     * @return array
     */
    private function getWebsiteCodes(): array
    {
        $websiteCodes = [];
        if ($this->storeManager->isSingleStoreMode()) {
            $websiteCodes[] = $this->storeManager->getDefaultStoreView()->getWebsite()->getCode();
        } else {
            foreach ($this->getStores() as $store) {
                if ($websiteCode = $store->getWebsite()->getCode()) {
                    $websiteCodes[] = $websiteCode;
                }
            }
        }

        return array_unique($websiteCodes);
    }
}
