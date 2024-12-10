<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Model\ResourceModel;

class Offer extends \Mirakl\Connector\Model\ResourceModel\Offer
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        /** @var \Mirakl\Connector\Model\Offer $object */
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(
                ['offer_index' => $this->getTable('mirakl_offer_index')],
                sprintf(
                    '%s.offer_id = offer_index.offer_id AND offer_index.store_id = %d',
                    $this->getMainTable(),
                    $object->getStoreId()
                )
            );
        }

        return $select;
    }
}
