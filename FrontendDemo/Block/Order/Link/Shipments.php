<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Order\Link;

class Shipments extends \Mirakl\FrontendDemo\Block\Order\Link
{
    /**
     * @return bool
     */
    public function isEnableMultiShipments()
    {
        return $this->connectorConfig->isEnableMultiShipments();
    }

    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _toHtml()
    {
        if (!$this->isEnableMultiShipments()) {
            return '';
        }

        return parent::_toHtml();
    }
}
