<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Repository;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Mirakl\Mcm\Model\Product\Import\Loader\LoaderInterface;

class Product
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoaderInterface
     */
    private $productLoader;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param LoaderInterface   $productLoader
     * @param int               $chunkSize
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        LoaderInterface $productLoader,
        int $chunkSize = 500
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productLoader = $productLoader;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param array  $values
     * @param string $index
     * @return array
     */
    public function load(array $values, string $index): array
    {
        if (!$values) {
            return [];
        }

        // Deduplication is done using attribute codes in provided values
        $deduplicationAttributes = array_keys(reset($values));

        // We first load products by $index
        // We split the product collection to specified chunk size for better performances
        foreach (array_chunk($values, $this->chunkSize) as $valuesChunk) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            // We filter values to remove null $index entries
            $collection->addAttributeToFilter($index, ['in' => array_filter(array_column($valuesChunk, $index))]);
            $products = $this->productLoader->load($collection);

            // We keep loaded products
            foreach ($products as $product) {
                if (!empty($product[$index])) {
                    $this->products[$product[$index]] = $product;
                }
            }
        }

        // We remove loaded products from initial $values by index
        foreach ($values as $counter => $value) {
            if (isset($value[$index]) && isset($this->products[$value[$index]])) {
                unset($values[$counter]);
            }
        }

        // Load products by other deduplication attributes if there is any
        $deduplicationAttributes = array_diff($deduplicationAttributes, [$index]);

        foreach ($deduplicationAttributes as $deduplicationAttribute) {
            // Stop if all products were already loaded by $index
            if (!$values) {
                break;
            }

            // Reset values keys
            $values = array_values($values);

            // Load products by $deduplicationAttribute by chunk
            $deduplicationAttributeValues = array_column($values, $deduplicationAttribute);
            foreach (array_chunk($values, $this->chunkSize) as $valuesChunk) {
                /** @var Collection $collection */
                $collection = $this->collectionFactory->create();
                $filteredValues = array_filter(array_column($valuesChunk, $deduplicationAttribute));
                if (!$filteredValues) {
                    continue;
                }

                $collection->addAttributeToFilter($deduplicationAttribute, ['in' => $filteredValues]);
                $products = $this->productLoader->load($collection);
                foreach ($products as $product) {
                    if (!isset($product[$deduplicationAttribute])) {
                        continue;
                    }
                    $key = array_search($product[$deduplicationAttribute], $deduplicationAttributeValues);
                    if ($key !== false && isset($values[$key][$index])) {
                        $this->products[$values[$key][$index]] = $product;
                        unset($values[$key]);
                    }
                }
            }
        }

        return $this->products;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->products;
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        return $this->products[$key] ?? null;
    }
}