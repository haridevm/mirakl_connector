<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mcm\Helper\Config;
use Mirakl\Mcm\Model\Product\Import\Data\Cleaner;

class Manager implements ManagerInterface
{
    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    /**
     * @var Formatter
     */
    private $dataFormatter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param DataSourceInterface $dataSource
     * @param Formatter $dataFormatter
     * @param Config $config
     */
    public function __construct(
        DataSourceInterface $dataSource,
        Formatter $dataFormatter,
        Config $config
    ) {
        $this->dataSource = $dataSource;
        $this->dataFormatter = $dataFormatter;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function add(array $data): void
    {
        // Write the main data to the source
        $this->push($data);

        $dataLocalized = $data[Cleaner\LocalizedAttributes::I18N_FIELD] ?? [];

        foreach ($this->getStores() as $store) {
            $locale = $this->config->getLocale($store);
            if (!empty($dataLocalized[$locale])) {
                // Write data for a specific store to the source
                $this->push($data, $store);
            }
        }
    }

    /**
     * @return StoreInterface[]
     */
    private function getStores()
    {
        return $this->config->getStoresUsedForProductImport(false);
    }

    /**
     * @param array $data
     * @param StoreInterface|null $store
     */
    private function push(array $data, ?StoreInterface $store = null): void
    {
        $this->dataSource->write(
            $this->dataFormatter->format($data, $store)
        );
    }

    /**
     * @inheritdoc
     */
    public function getDataSource(): DataSourceInterface
    {
        return $this->dataSource;
    }
}