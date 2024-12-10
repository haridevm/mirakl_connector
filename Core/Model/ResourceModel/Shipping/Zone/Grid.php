<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Shipping\Zone;

class Grid extends Collection
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addStoresToResult();

        return $this;
    }
}
