<?php

declare(strict_types=1);

namespace Mirakl\Connector\Block\Adminhtml\Offer;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _prepareCollection()
    {
        /** @var \Mirakl\Connector\Model\ResourceModel\Offer\Collection $collection */
        $collection = $this->getCollection();
        $collection->joinProductIds(true);
        $collection->addProductNames();

        return parent::_prepareCollection();
    }
}
