<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Shop;

class ContactInfo extends View
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _setTabTitle()
    {
        $title = __('Contact Information');
        $this->setTitle($title);
    }
}
