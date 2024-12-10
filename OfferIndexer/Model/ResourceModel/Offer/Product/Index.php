<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Index extends AbstractDb
{
    const MAIN_TABLE_ALIAS = 'offer_product_index';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_setMainTable('mirakl_offer_product_index');
    }

    /**
     * Updates specified rows
     *
     * @param array $rows
     * @param int $chunkSize
     * @return int
     */
    public function update(array $rows, int $chunkSize = 1000): int
    {
        $affected = 0;
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $affected += $this->getConnection()->insertOnDuplicate($this->getMainTable(), $chunk);
        }

        return $affected;
    }

    /**
     * Clear all or some rows by SKUs
     *
     * @param array $skus
     * @param int $chunkSize
     * @return void
     */
    public function clear(array $skus = [], int $chunkSize = 1000): void
    {
        if (empty($skus)) {
            $this->truncate();
        } else {
            foreach (array_chunk($skus, $chunkSize) as $chunk) {
                $this->getConnection()->delete($this->getMainTable(), ['sku IN (?)' => $chunk]);
            }
        }
    }

    /**
     * Truncates table
     *
     * @return void
     */
    protected function truncate(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
