<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\DB\Trigger\Creator;

class StockStatusTriggerCreator extends AbstractTriggerCreator
{
    /**
     * @inheritdoc
     */
    public function create(int $stockId): string
    {
        return <<<SQL
IF NEW.stock_status = 0 THEN
    SET @count_offers = (
        SELECT COUNT(offer_id)
        FROM {$this->quoteTable('mirakl_offer_product_stock_index')}
        WHERE product_id = NEW.product_id AND stock_id = $stockId
    );
    IF @count_offers > 0 THEN
        SET NEW.stock_status = 1;
    END IF;
END IF;
SQL;
    }
}
