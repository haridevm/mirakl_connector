<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\ResourceModel\Offer;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Index extends AbstractDb
{
    public const MAIN_TABLE_ALIAS = 'offer_index';

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_setMainTable('mirakl_offer_index');
    }

    /**
     * Updates specified rows
     *
     * @param array $rows
     * @param int   $chunkSize
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
     * @param int   $chunkSize
     * @return void
     */
    public function clear(array $skus = [], int $chunkSize = 1000): void
    {
        if (empty($skus)) {
            $this->truncate();
        } else {
            foreach (array_chunk($skus, $chunkSize) as $chunk) {
                $this->deleteBySkus($chunk);
            }
        }
    }

    /**
     * @param array $skus
     * @return void
     */
    protected function deleteBySkus(array $skus): void
    {
        if (empty($skus)) {
            return; // @codeCoverageIgnore
        }

        $select = $this->getConnection()->select()
            ->from(['index' => $this->getMainTable()])
            ->join(
                ['offer' => $this->getTable('mirakl_offer')],
                'index.offer_id = offer.offer_id',
                ['offer.offer_id']
            )
            ->where('offer.product_sku IN (?)', $skus);

        $sql = $select->deleteFromSelect('index');
        $this->getConnection()->query($sql);
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
