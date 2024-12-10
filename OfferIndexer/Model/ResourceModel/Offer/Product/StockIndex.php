<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\ResourceModel\Offer\Product;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StockIndex extends AbstractDb
{
    const MAIN_TABLE_ALIAS = 'offer_product_stock_index';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_setMainTable('mirakl_offer_product_stock_index');
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
     * Clear all or some rows by product ids
     *
     * @param array $productIds
     * @param int $chunkSize
     * @return void
     */
    public function clear(array $productIds = [], int $chunkSize = 1000): void
    {
        if (empty($productIds)) {
            $this->truncate();
        } else {
            foreach (array_chunk($productIds, $chunkSize) as $chunk) {
                $this->getConnection()->delete($this->getMainTable(), ['product_id IN (?)' => $chunk]);
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
