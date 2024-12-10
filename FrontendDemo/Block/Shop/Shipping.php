<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Shop;

class Shipping extends View
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _setTabTitle()
    {
        $title = __('Shipping Methods');
        $this->setTitle($title);
    }

    /**
     * @return array
     */
    public function getShippingMethods()
    {
        $_shop = $this->getShop();
        $_shippingInfo = [];
        foreach ($_shop->getAdditionalInfo()['shipping_info']['shipping_rules'] as $_rule) {
            $_shippingInfo[$_rule['shipping_zone']['label']][] = $_rule['shipping_type']['label'];
        }

        return $_shippingInfo;
    }
}
