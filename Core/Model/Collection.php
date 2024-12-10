<?php

declare(strict_types=1);

namespace Mirakl\Core\Model;

use Mirakl\Core\Domain\MiraklObject;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_itemObjectClass = MiraklObject::class;

    /**
     * @inheritdoc
     */
    public function setItems(array $items)
    {
        $this->_items = $items;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTotalRecords($count)
    {
        $this->_totalRecords = $count;

        return $this;
    }
}
