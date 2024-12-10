<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Product\Export;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Connector\Model\Product\Filter;
use Mirakl\Core\Model\ResourceModel\Product\Collection as ProductCollection;
use Mirakl\Core\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirakl\Mci\Model\Product\Attribute\ProductAttributesFinder;
use Mirakl\Mcm\Helper\Config;

class AddLocalizableValuesObserver implements ObserverInterface
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductAttributesFinder
     */
    private $productAttributesFinder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Filter\Description
     */
    private $descriptionFilter;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductAttributesFinder  $productAttributesFinder
     * @param Config                   $config
     * @param Filter\Description       $descriptionFilter
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductAttributesFinder $productAttributesFinder,
        Config $config,
        Filter\Description $descriptionFilter
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAttributesFinder  = $productAttributesFinder;
        $this->config                   = $config;
        $this->descriptionFilter        = $descriptionFilter;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $attributes = $this->getLocalizableAttributesCodes();

        if (empty($attributes)) {
            return;
        }

        $eventData = $observer->getEvent()->getData();
        $data = &$eventData['products'];

        foreach ($this->getStores() as $locale => $store) {
            /** @var ProductCollection $collection */
            $collection = $this->productCollectionFactory->create();

            $collection->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns(['entity_id']);

            $collection->addIdFilter(array_keys($data));
            $collection->setStoreId($store->getId());
            $collection->addAttributeToSelect($attributes);

            foreach ($collection as $product) {
                $productId = $product['entity_id'];
                if (!isset($data[$productId])) {
                    continue;
                }
                foreach ($product as $field => $value) {
                    if (in_array($field, $attributes)) {
                        $data[$productId][$field . '-' . $locale] = $this->format($field, $value);
                    }
                }
            }
        }
    }

    /**
     * @param string $field
     * @param string $value
     * @return string
     */
    private function format($field, $value)
    {
        switch ($field) {
            case 'description':
                return $this->descriptionFilter->filter($value);
            default:
                return $value;
        }
    }

    /**
     * Retrieve stores sorted by locale
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    private function getStores()
    {
        $stores = [];

        foreach ($this->config->getStoresUsedForProductImport(false) as $store) {
            $locale = $this->config->getLocale($store);
            if (!isset($stores[$locale])) {
                $stores[$locale] = $store;
            }
        }

        return $stores;
    }

    /**
     * @return string[]
     */
    private function getLocalizableAttributesCodes()
    {
        return array_map(function (EavAttribute $attribute) {
            return $attribute->getAttributeCode();
        }, $this->productAttributesFinder->getExportableAttributesLocalizable());
    }
}
