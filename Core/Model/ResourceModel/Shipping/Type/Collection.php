<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Shipping\Type;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_init(\Mirakl\Core\Model\Shipping\Type::class, \Mirakl\Core\Model\ResourceModel\Shipping\Type::class);
    }
}
