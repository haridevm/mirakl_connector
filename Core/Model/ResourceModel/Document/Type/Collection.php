<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Document\Type;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_init(\Mirakl\Core\Model\Document\Type::class, \Mirakl\Core\Model\ResourceModel\Document\Type::class);
    }
}
