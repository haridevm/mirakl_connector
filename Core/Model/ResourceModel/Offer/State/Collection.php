<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Offer\State;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_idFieldName = \Mirakl\Core\Model\Offer\State::STATE_ID;

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_init(\Mirakl\Core\Model\Offer\State::class, \Mirakl\Core\Model\ResourceModel\Offer\State::class);
    }
}
