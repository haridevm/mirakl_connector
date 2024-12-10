<?php
namespace Mirakl\Core\Model\ResourceModel\Shipping\Type;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Set resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Mirakl\Core\Model\Shipping\Type::class, \Mirakl\Core\Model\ResourceModel\Shipping\Type::class);
    }
}
