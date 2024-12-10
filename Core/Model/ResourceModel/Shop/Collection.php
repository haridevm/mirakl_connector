<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Shop;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirakl\Core\Model\Shop;

/**
 * @method Shop getFirstItem()
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = Shop::SHOP_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Shop::class, \Mirakl\Core\Model\ResourceModel\Shop::class);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        /** @var Shop $item */
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }

        return parent::_afterLoad();
    }
}
